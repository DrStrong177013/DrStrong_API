<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OllamaService;
class AskController extends Controller
{
    private OllamaService $ollamaSevice;

    public function _construct(OllamaService $ollamaSevice){
        $this->ollamaSevice = $ollamaSevice;
    }


    public function __invoke(Request $request)
    {
        Log::info($request->all());
        
        $request->validate([
            'role_description' => 'required|string|min:3|max:2000',
            'question' => 'required|string|min:3|max:2000',
        ]);

        $response =$this->ollamaService->ask($request);
        Log::info($response);
        return $response;
    }
}
