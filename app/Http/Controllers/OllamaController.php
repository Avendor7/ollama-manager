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
use App\Services\OllamaService;
class OllamaController extends Controller
{
    use AuthorizesRequests;
    public OllamaService $ollamaService;
    public function __construct(OllamaService $ollamaService)
    {
        $this->ollamaService = $ollamaService;
    }

    public function index()
    {
        // Get the current user
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $modelList = $this->ollamaService->getModelList();
        $runningList = $this->ollamaService->getRunningList();
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
            'modelList' => $modelList,
            'runningList' => $runningList,
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

        // Redirect to the dashboard route with the new chat ID as a query parameter
        return redirect()->route('dashboard', ['chat_id' => $chatSession->id]);
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

    public function loadModel(Request $request){

        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $model = $request->input('model');
        $this->ollamaService->loadModel($model);
        return to_route('dashboard');
    }

    public function unloadModel(Request $request){

        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $model = $request->input('model');
        $this->ollamaService->unloadModel($model);
        return to_route('dashboard');
    }

    /**
     * Get the currently running model
     * This endpoint is used for polling the running model status
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRunningModel()
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $runningList = $this->ollamaService->getRunningList();
        return response()->json($runningList);
    }
}
