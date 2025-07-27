<?php

namespace App\Http\Controllers;

use App\Services\ChatService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Message;
use App\Models\ChatSession;
use App\Services\OllamaService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    public OllamaService $ollamaService;
    public function __construct(OllamaService $ollamaService)
    {
        $this->ollamaService = $ollamaService;
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $modelList = $this->ollamaService->getModelList();
        $runningList = $this->ollamaService->getRunningList();
        $chatSessions = ChatSession::where('user_id', $user->id)
                                    ->orderByDesc('created_at')
                                    ->get();

        $currentChatId = $request->query('chat_id');
        $currentChatSession = $currentChatId
            ? ChatSession::where('id', $currentChatId)->where('user_id', $user->id)->first()
            : $chatSessions->first();

        if (!$currentChatSession && $chatSessions->isEmpty()) {
            $currentChatSession = ChatSession::create([
                'user_id' => $user->id,
                'title' => 'New Chat',
                'is_active' => true,
            ]);
        } elseif (!$currentChatSession) {
            $currentChatSession = $chatSessions->first();
        }


        $messages = [];
        if ($currentChatSession) {
            $messages = Message::where('chat_session_id', $currentChatSession->id)
                              ->orderBy('created_at')
                              ->get();
        }

        return Inertia::render('Dashboard', [
            'user' => $user,
            'messages' => $messages,
            'chatSessions' => $chatSessions,
            'currentChatId' => $currentChatSession?->id,
            'modelList' => $modelList,
            'runningList' => $runningList,
        ]);
    }

    public function createNewChat(Request $request)
    {
        $user = $request->user();

        $chatSession = ChatSession::create([
            'user_id' => $user->id,
            'title' => 'New Chat',
            'is_active' => true,
        ]);

        return redirect()->route('dashboard', ['chat_id' => $chatSession->id]);
    }

    public function destroy(ChatSession $chat): RedirectResponse
    {
        $chat->delete();

        return redirect()->route('dashboard');
    }

    public function handleChatStream(Request $request): StreamedResponse
    {
        $chatSession = ChatSession::findOrFail($request->input('chat_session_id'));
        $prompt = $request->input('prompt');

        $chatService = app(ChatService::class);

        $chatService->saveUserMessage($chatSession, $prompt);

        return response()->stream(function () use ($chatService, $chatSession, $prompt) {
            yield from $chatService->generateStreamResponse($chatSession, $prompt);
        }, headers: [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/stream',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
