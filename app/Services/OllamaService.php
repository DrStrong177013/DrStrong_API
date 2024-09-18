<?php

namespace App\Services;

use Cloudstudio\Ollama\Facades\Ollama;
use Illuminate\Http\Request;

class OllamaService
{
    public function ask(Request $request)
    {
        $response = Ollama::agent($request->role_description)
    ->prompt($request->question.' '.'Respond using Json')
    ->format('json')
    ->model('ollama-lavarel.model')
    ->options(floatval('ollama-lavarel.temperature'))
    ->stream(false)
    ->ask();
    return response()->json($response , 200);
    }
}
