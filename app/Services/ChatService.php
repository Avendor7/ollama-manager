<?php

namespace App\Services;

use App\Models\ChatSession;
use App\Models\Message;
use Generator;
use Prism\Prism\Prism;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;

class ChatService
{
    public function __construct()
    {
    }

    public function saveUserMessage(ChatSession $chatSession, string $prompt): Message
    {
        return Message::create([
            'chat_session_id' => $chatSession->id,
            'content' => $prompt,
            'role' => 'user',
        ]);
    }

    public function generateStreamResponse(ChatSession $chatSession, string $prompt): Generator
    {
        $messages = $this->buildMessageHistory($chatSession, $prompt);

        $stream = Prism::text()
            ->using('ollama', 'llama3.2:1b')
            ->withMessages($messages)
            ->asStream();

        $fullResponse = '';
        foreach ($stream as $response) {
            $fullResponse .= $response->text;
            yield $response->text;
        }

        // Save the assistant's response
        $this->saveAssistantMessage($chatSession, $fullResponse);

        // Generate title if this is the first exchange
        $this->generateTitleIfNeeded($chatSession);
    }

    private function buildMessageHistory(ChatSession $chatSession, string $newPrompt): array
    {
        $historicalMessages = $chatSession->messages()
            ->orderBy('created_at')
            ->get()
            ->map(fn($msg) => $msg->role === 'user'
                ? new UserMessage($msg->content)
                : new AssistantMessage($msg->content)
            )
            ->toArray();

        return [
            ...$historicalMessages,
            new UserMessage($newPrompt),
        ];
    }

    private function saveAssistantMessage(ChatSession $chatSession, string $content): Message
    {
        return Message::create([
            'chat_session_id' => $chatSession->id,
            'content' => $content,
            'role' => 'assistant',
        ]);
    }

    private function generateTitleIfNeeded(ChatSession $chatSession): void
    {
        if ($chatSession->messages()->count() === 2) {
            $chatSession->generateTitle();
        }
    }
}
