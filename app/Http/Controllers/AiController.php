<?php

namespace App\Http\Controllers;

use App\Services\AiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiController extends Controller
{
    public function __construct(protected AiService $aiService)
    {
    }

    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prompt' => ['required', 'string', 'max:2000'],
            'model' => ['nullable', 'string', 'max:100'],
            'max_tokens' => ['nullable', 'integer', 'min:1', 'max:4096'],
            'temperature' => ['nullable', 'numeric', 'min:0', 'max:2'],
        ]);

        $messages = [
            [
                'role' => 'user',
                'content' => $validated['prompt'],
            ],
        ];

        $model = $validated['model'] ?? null;
        $maxTokens = $validated['max_tokens'] ?? 512;
        $temperature = $validated['temperature'] ?? 0.7;

        try {
            $content = $this->aiService->generateText($messages, $model, $maxTokens, $temperature);

            return response()->json([
                'output' => $content,
                'model' => $model ?? config('services.openai.default_model'),
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'error' => 'AI request failed: ' . $e->getMessage(),
                'base_url' => config('services.openai.base_url'),
            ], 500);
        }
    }
}
