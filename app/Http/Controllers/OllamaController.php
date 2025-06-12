<?php

namespace App\Http\Controllers;

use App\Services\ChatService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Message;
use App\Models\ChatSession;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OllamaController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        // Get the current user
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        // Retrieve all chat sessions for the user
        $chatSessions = ChatSession::where('user_id', $user->id)
                                    ->orderByDesc('created_at')
                                    ->get();

        // Get the current chat session (either from query param or most recent)
        $currentChatId = request()->query('chat_id');
        $currentChatSession = $currentChatId
            ? ChatSession::where('id', $currentChatId)->first()
            : $chatSessions->first();

        // If no chat session exists, create a new one
        if (!$currentChatSession) {
            $currentChatSession = ChatSession::create([
                'user_id' => $user->id,
                'title' => 'New Chat',
                'is_active' => true,
            ]);
        }

        $messages = [];
        if ($currentChatSession) {
            // Fetch all messages for that chat
            $messages = Message::where('chat_session_id', $currentChatSession->id)
                              ->orderBy('created_at')
                              ->get();
        }

        return Inertia::render('Dashboard', [
            'user' => $user,
            'messages' => $messages,
            'chatSessions' => $chatSessions,
            'currentChatId' => $currentChatSession?->id,
        ]);
    }

    public function createNewChat()
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        // Create a new chat session
        $chatSession = ChatSession::create([
            'user_id' => $user->id,
            'title' => 'New Chat',
            'is_active' => true,
        ]);

        // Return a fresh state with empty messages
        return Inertia::render('Dashboard', [
            'user' => $user,
            'messages' => [],
            'chatSessions' => ChatSession::where('user_id', $user->id)
                                        ->orderByDesc('created_at')
                                        ->get(),
            'currentChatId' => $chatSession->id,
        ]);
    }

    /**
     * @param ChatSession $chat
     * @return RedirectResponse
     */
    public function destroy(ChatSession $chat){
        //$this->authorize('delete', $chat);

        $chat->delete();

        return redirect()->route('dashboard');

    }

    public function handleChatStream(Request $request): StreamedResponse
    {
        $chatSession = ChatSession::findOrFail($request->input('chat_session_id'));
        $prompt = $request->input('prompt');

        // Inject the ChatService
        $chatService = app(ChatService::class);

        // Save the user message immediately
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
