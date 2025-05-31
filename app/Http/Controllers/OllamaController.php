<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use ArdaGnsrn\Ollama\Ollama;
use Prism\Prism\Prism;

class OllamaController extends Controller
{
    public function index()
    {
        //$client = Ollama::client();
        //$response = $client->models()->list();
        //Log::debug(json_encode($response));
        return Inertia::render('Dashboard');
    }


    public function prismChat(Request $request){

        return response()->eventStream(function () {
            $stream = Prism::text()
                ->using('ollama', 'qwen2.5:14b')
                ->withPrompt('Explain quantum computing step by step.')
                ->asStream();

            foreach ($stream as $response) {
                yield $response->text;
            }
        });
    }


    public function chat(Request $request)
    {
        $data = $request->validate([
            'message' => 'required|string',
            'conversation' => 'nullable|array',
        ]);

        try {
            $client = Ollama::client();
            $conversation = $data['conversation'] ?? [];
            $userMessage = [
                'role' => 'user',
                'content' => $data['message'],
            ];
            $messages = array_merge($conversation, [$userMessage]);

            // Call Ollama chat endpoint
            $response = $client->chat()->create([
                'model' => 'llama3', // You can adjust this to your default model
                'messages' => $messages,
            ]);

            return response()->json([
                'response' => $response['message']['content'] ?? 'No response',
                'conversation' => array_merge($messages, [
                    [
                        'role' => 'assistant',
                        'content' => $response['message']['content'] ?? 'No response',
                    ]
                ])
            ]);
        } catch (\Throwable $e) {
            Log::error('Ollama chat error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to chat with Ollama'], 500);
        }
    }
}

