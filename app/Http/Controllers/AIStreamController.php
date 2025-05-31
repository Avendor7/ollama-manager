<?php

namespace App\Http\Controllers;

use App\Services\PrismService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Prism\Prism\Prism;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AIStreamController extends Controller
{
    public function index(){
        return Inertia::render('AIstream');

    }
    public function streamCompose(Request $request, PrismService $prism): StreamedResponse
    {
        $request->validate(['prompt' => 'required|string']);
        return response()->eventStream(function () use ($request, $prism) {
            $stream = Prism::text()
                ->using('ollama', 'qwen2.5:14b')
                ->withPrompt($request->input('prompt'))
                ->asStream();

            foreach ($stream as $response) {
                yield $response->text;
            }
        });
    }
}
