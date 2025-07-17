<?php

namespace App\Services;

use ArdaGnsrn\Ollama\Ollama;

class OllamaService
{
    public function __construct()
    {
    }

    public function getModelList(){
        $client = Ollama::client(config('app.ollama_api_endpoint'));
        return $client->models()->list();
    }

    public function getRunningList(){
        // Cache the running list for 1 minute to reduce API calls
        return cache()->remember('ollama_running_list', 60, function () {
            $client = Ollama::client(config('app.ollama_api_endpoint'));
            return $client->models()->runningList();
        });
    }

    public function loadModel($model){
        $client = Ollama::client(config('app.ollama_api_endpoint'));
        return $client->models()->load($model);
    }

    public function unloadModel($model){
        $client = Ollama::client(config('app.ollama_api_endpoint'));
        return $client->models()->unload($model);
    }
}
