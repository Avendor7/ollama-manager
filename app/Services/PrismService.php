<?php

namespace App\Services;

use Generator;
use Prism\Prism\Prism;

class PrismService
{
    protected $prism;

    public function __construct()
    {
        $this->prism = new Prism();
        // $this->prism->setApiKey(config('prism.api_key'));
    }

    public function streamContent(string $prompt): Generator
    {
        return response()->eventStream(function () {
            $stream = Prism::text()
                ->using('ollama', 'qwen2.5:14b')
                ->withPrompt($prompt)
                ->asStream();

            foreach ($stream as $response) {
                yield $response->text;
            }
        });
    }
}
