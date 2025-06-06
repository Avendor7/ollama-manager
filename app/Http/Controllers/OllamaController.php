<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Message;
use App\Models\ChatSession;
use Generator;
use Prism\Prism\Prism;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OllamaController extends Controller
{
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

        // Create new chat session
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

    public function handleChatStream(Request $request): StreamedResponse
    {
        $chatSession = ChatSession::findOrFail($request->input('chat_session_id'));

        // Save the user message
        Message::create([
            'chat_session_id' => $chatSession->id,
            'content' => $request->input('prompt'),
            'role' => 'user',
        ]);


        \Log::debug(
            ...$chatSession->messages()
            ->orderBy('created_at')
            ->get()
            ->map(fn($msg) => [
                'role' => $msg->role,
                'content' => $msg->content,
            ])
            ->toArray());

        return response()->stream(function () use ($request, $chatSession): Generator {
            $stream = Prism::text()
                ->using('ollama', 'llama3.1:8b')
                ->withMessages([
                    new UserMessage($request->input('prompt')),
                ])
                ->asStream();

            $fullResponse = '';
            foreach ($stream as $response) {
                $fullResponse .= $response->text;
                yield $response->text;
            }
            // Save the assistant's response
            Message::create([
                'chat_session_id' => $chatSession->id,
                'content' => $fullResponse,
                'role' => 'assistant',
            ]);

            // Generate title if this is the first message
            if ($chatSession->messages()->count() === 2) {
                $chatSession->generateTitle();
            }
        }, status: 200, headers: [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/stream',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
