<?php

namespace App\Services;

use OpenAI\Client;
use OpenAI\Factory;
use Illuminate\Support\Facades\Log;

class AiService
{
    protected Client $client;
    protected string $defaultModel;
    protected string $baseUrl;

    public function __construct()
    {
        $apiKey = config('services.openai.key');
        if (empty($apiKey)) {
            throw new \RuntimeException('OPENAI_API_KEY is not configured.');
        }

        // Normalize base URL: strip quotes/whitespace, enforce scheme, ensure /v1 suffix
        $baseUrl = trim(config('services.openai.base_url') ?? '', " \t\n\r\0\x0B\"'");
        if ($baseUrl === '') {
            $baseUrl = 'https://api.openai.com/v1';
        }
        if (!str_starts_with($baseUrl, 'http://') && !str_starts_with($baseUrl, 'https://')) {
            $baseUrl = 'https://' . ltrim($baseUrl, '/');
        }
        // Ensure it ends with /v1 for OpenAI-compatible providers
        if (!str_ends_with($baseUrl, '/v1')) {
            $baseUrl = rtrim($baseUrl, '/') . '/v1';
        }

        // Validate URL to avoid PSR-7 Uri parse errors
        if (!filter_var($baseUrl, FILTER_VALIDATE_URL)) {
            throw new \RuntimeException('OPENAI_BASE_URL is invalid: ' . $baseUrl);
        }

        $this->baseUrl = $baseUrl;

        $this->client = (new Factory())
            ->withApiKey($apiKey)
            ->withBaseUri($baseUrl)
            ->make();

        $this->defaultModel = config('services.openai.default_model', 'gpt-4.1');
    }

    /**
     * Generate text completion from chat messages.
     *
     * @param array<int, array{role:string, content:string}> $messages
     */
    public function generateText(array $messages, ?string $model = null, int $maxTokens = 512, float $temperature = 0.7): string
    {
        $modelToUse = $model ?: $this->defaultModel;

        try {
            $response = $this->client->chat()->create([
                'model' => $modelToUse,
                'messages' => $messages,
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
            ]);

            return $response->choices[0]->message->content ?? '';
        } catch (\Throwable $e) {
            Log::error('AI generation failed', [
                'error' => $e->getMessage(),
                'model' => $modelToUse,
                'base_url' => $this->baseUrl,
            ]);

            throw new \RuntimeException($e->getMessage() . ' | base_url=' . $this->baseUrl, previous: $e);
        }
    }
}
