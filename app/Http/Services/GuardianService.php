<?php

namespace App\Http\Services;

use App\Http\Contracts\NewsServiceInterface;

class GuardianService extends BaseNewsService implements NewsServiceInterface
{

    protected function initializeConfig(): void
    {
        $this->serviceName = "guardian";
        $this->baseUrl = config('services.guardian.base_url');
        $this->apiKey = config('services.guardian.key');
        $this->rateLimit = config('services.guardian.rate_limit');
        $this->apiKeyParamName = config('services.guardian.api_key_param');
    }

    public function fetchArticles(string $category = null): array
    {
        $params = [
            'show-fields' => 'all',
            'show-tags' => 'all',
            'page-size' => 50,
            'order-by' => 'newest',
        ];

        if ($category) {
            $params['section'] = $this->mapCategory($category);
        }

        $response = $this->makeRequest('search', $params);

        if (!$response || $response['response']['status'] !== 'ok') {
            return [];
        }

        return array_map([$this, 'mapArticle'], $response['response']['results'] ?? []);
    }

    protected function mapArticle(array $rawArticle): array
    {
        return [
            'external_id' => $rawArticle['id'] ?? null,
            'title' => $rawArticle['webTitle'] ?? '',
            'summary' => $rawArticle['fields']['trailText'] ?? '',
            'content' => $rawArticle['fields']['bodyText'] ?? '',
            'url' => $rawArticle['webUrl'] ?? '',
            'image_url' => $rawArticle['fields']['thumbnail'] ?? null,
            'author' => $rawArticle['fields']['byline'] ?? 'The Guardian',
            'published_at' => $rawArticle['webPublicationDate'] ?? now(),
            'source_metadata' => $rawArticle,
        ];
    }

    private function mapCategory(string $genericCategory): string
    {
        $mapping = [
            'technology' => 'technology',
            'business' => 'business',
            'sports' => 'sport',
            'entertainment' => 'culture',
            'health' => 'society',
            'science' => 'science',
            'general' => 'world',
        ];

        return $mapping[$genericCategory] ?? $genericCategory;
    }

    public function getSource(): string
    {
        return $this->serviceName;
    }
}
