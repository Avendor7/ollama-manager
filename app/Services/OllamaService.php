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

}
