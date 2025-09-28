<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class BaseNewsService
{
    protected string $serviceName;
    protected string $baseUrl;
    protected string $apiKey;
    protected int $rateLimit;
    protected string $apiKeyParamName = 'apiKey';

    public function __construct()
    {
        $this->initializeConfig();
    }

    abstract protected function initializeConfig(): void;
    abstract protected function mapArticle(array $rawArticle): array;

    protected function makeRequest(string $endpoint, array $params = []): ?array
    {
        try {
            $apiKeyParam = [$this->apiKeyParamName => $this->apiKey];

            $fullUrl = $this->baseUrl . $endpoint;

            if (str_contains($endpoint, '?')) {
                $fullUrl .= '&' . http_build_query(array_merge($params, $apiKeyParam));
            } else {
                $fullUrl .= '?' . http_build_query(array_merge($params, $apiKeyParam));
            }

            $response = Http::timeout(30)
                ->retry(3, 100)
                ->get($fullUrl);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error("API Request failed: {$response->status()}", [
                'service' => static::class,
                'endpoint' => $endpoint,
                'status' => $response->status()
            ]);
            return null;

        } catch (\Exception $e) {
            Log::error("API Request exception: {$e->getMessage()}", [
                'service' => static::class,
                'endpoint' => $endpoint
            ]);
            return null;
        }
    }
}
