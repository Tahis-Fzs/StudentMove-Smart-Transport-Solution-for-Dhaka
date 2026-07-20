<?php

namespace App\Services;

use OpenAI\Client;
use OpenAI\Factory;
use Illuminate\Support\Facades\Log;

class AiService
{
    protected ?Client $client = null;
    protected string $defaultModel;
    protected string $baseUrl = '';
    protected ?string $initError = null;

    public function __construct()
    {
        $this->defaultModel = config('services.openai.default_model', 'meta-llama/llama-3.1-8b-instruct:free');
    }

    public function isConfigured(): bool
    {
        return !empty(config('services.openai.key'));
    }

    protected function client(): Client
    {
        if ($this->client) {
            return $this->client;
        }

        $apiKey = config('services.openai.key');
        if (empty($apiKey)) {
            throw new \RuntimeException('OPENAI_API_KEY is not configured.');
        }

        $baseUrl = trim((string) (config('services.openai.base_url') ?? ''), " \t\n\r\0\x0B\"'");
        if ($baseUrl === '') {
            $baseUrl = 'https://openrouter.ai/api/v1';
        }
        if (!str_starts_with($baseUrl, 'http://') && !str_starts_with($baseUrl, 'https://')) {
            $baseUrl = 'https://' . ltrim($baseUrl, '/');
        }
        if (!str_ends_with($baseUrl, '/v1')) {
            $baseUrl = rtrim($baseUrl, '/') . '/v1';
        }

        if (!filter_var($baseUrl, FILTER_VALIDATE_URL)) {
            throw new \RuntimeException('OPENAI_BASE_URL is invalid: ' . $baseUrl);
        }

        $this->baseUrl = $baseUrl;

        $factory = (new Factory())
            ->withApiKey($apiKey)
            ->withBaseUri($baseUrl);

        // OpenRouter expects these headers for rankings / free models
        if (str_contains($baseUrl, 'openrouter.ai')) {
            $factory = $factory
                ->withHttpHeader('HTTP-Referer', config('app.url', 'http://localhost'))
                ->withHttpHeader('X-Title', config('app.name', 'StudentMove'));
        }

        $this->client = $factory->make();

        return $this->client;
    }

    /**
     * Generate text completion from chat messages.
     *
     * @param array<int, array{role:string, content:string}> $messages
     */
    public function generateText(array $messages, ?string $model = null, int $maxTokens = 512, float $temperature = 0.7): string
    {
        if (!$this->isConfigured()) {
            return $this->localFallback($messages);
        }

        $modelToUse = $model ?: $this->defaultModel;

        try {
            $response = $this->client()->chat()->create([
                'model' => $modelToUse,
                'messages' => array_merge([
                    [
                        'role' => 'system',
                        'content' => 'You are StudentMove assistant for Dhaka student transport. Be concise and practical. Help with bus routes, schedules, fares, and campus commute tips (DU, DIU/DSC, BUET, NSU, etc.).',
                    ],
                ], $messages),
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
            ]);

            return trim((string) ($response->choices[0]->message->content ?? ''));
        } catch (\Throwable $e) {
            Log::error('AI generation failed', [
                'error' => $e->getMessage(),
                'model' => $modelToUse,
                'base_url' => $this->baseUrl,
            ]);

            // Soft-fallback so the UI still helps users during local/demo use
            $fallback = $this->localFallback($messages);
            if ($fallback !== '') {
                return $fallback . "\n\n(Note: live AI provider unavailable right now.)";
            }

            throw new \RuntimeException($e->getMessage(), previous: $e);
        }
    }

    /**
     * Offline answers for common StudentMove questions when no API key is set.
     *
     * @param array<int, array{role:string, content:string}> $messages
     */
    public function localFallback(array $messages): string
    {
        $prompt = strtolower(trim((string) ($messages[array_key_last($messages)]['content'] ?? '')));

        if ($prompt === '') {
            return 'Ask me about routes, bus timing, fares, or campuses in Dhaka.';
        }

        if (str_contains($prompt, 'best route') || str_contains($prompt, 'route')) {
            return "For a solid student commute in Dhaka:\n"
                . "1) Open Routes and enter your current area + campus (e.g. Uttara → DSC).\n"
                . "2) Prefer Direct / Fastest in the morning peak (7–9 AM).\n"
                . "3) Check Live map for delays before you leave.\n"
                . "Tip: Uttara → DSC often takes ~40–55 min; Mirpur → DU ~30–45 min depending on traffic.";
        }

        if (str_contains($prompt, 'fare') || str_contains($prompt, 'price') || str_contains($prompt, 'cost') || str_contains($prompt, '৳')) {
            return "Typical StudentMove fares:\n"
                . "• Single ride: ৳30\n"
                . "• Weekly pass: ৳350\n"
                . "• Monthly pass: ৳1200\n"
                . "Open Plans to subscribe. Students who ride 4+ days/week usually save with Weekly or Monthly.";
        }

        if (str_contains($prompt, 'schedule') || str_contains($prompt, 'time') || str_contains($prompt, 'arrival') || str_contains($prompt, 'bus')) {
            return "Use Live map for day-wise schedules and next arrivals.\n"
                . "Peak buses fill early — leave 10–15 minutes earlier on exam days.\n"
                . "Last buses are usually around 9–10 PM depending on corridor.";
        }

        if (str_contains($prompt, 'subscribe') || str_contains($prompt, 'plan') || str_contains($prompt, 'pass')) {
            return "Go to Plans → pick Weekly (৳350), Monthly (৳1200), or Single Ride (৳30) → Checkout with Mobile Banking or Card.\n"
                . "Active passes appear on your Dashboard after payment confirms.";
        }

        return "I can help with:\n"
            . "• Best routes between areas/campuses\n"
            . "• Fares & subscription plans\n"
            . "• Bus timing / Live map tips\n\n"
            . "Try asking: \"best route from Uttara to DSC\" or \"which plan saves money?\"";
    }
}
