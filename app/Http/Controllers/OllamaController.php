<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Message;
use App\Models\ChatSession;
use Generator;
use Prism\Prism\Prism;
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

        // Retrieve the most recent chat session for the user
        $latestChatSession = ChatSession::where('user_id', $user->id)
                                        ->orderByDesc('created_at')
                                        ->first();

        $messages = [];
        if ($latestChatSession) {
            // Fetch all messages for that chat
            $messages = Message::where('chat_session_id', $latestChatSession->id)
                              ->get();
        }

        // Retrieve titles of all previous chats
        $previousChats = ChatSession::where('user_id', $user->id)
                                    ->orderByDesc('created_at')
                                    ->whereNotIn('id', [$latestChatSession ? $latestChatSession->id : 0])
                                    ->pluck('title');

        return Inertia::render('Dashboard', [
            'user' => $user,
            'messages' => $messages,
            'previousChats' => $previousChats,
        ]);
    }

    public function handleChatStream(Request $request): StreamedResponse
    {
        //save new chat to database
        //retreive all chats



        return response()->stream(function () use ($request): Generator {
            $stream = Prism::text()
                ->using('ollama', 'llama3.1:8b')
                ->withMessages([

                ])
                ->withPrompt($request->input("prompt"))
                ->asStream();

            foreach ($stream as $response) {
                yield $response->text;
            }
        }, status: 200, headers: [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/stream',
            'X-Accel-Buffering' => 'no',
        ]);
    }

}
