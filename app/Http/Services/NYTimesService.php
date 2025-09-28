<?php

namespace App\Http\Services;

use App\Http\Contracts\NewsServiceInterface;

class NYTimesService extends BaseNewsService implements NewsServiceInterface
{
    private array $categoryMapping = [
        'technology' => 'technology',
        'business' => 'business',
        'sports' => 'sports',
        'entertainment' => 'arts',
        'health' => 'health',
        'science' => 'science',
        'general' => 'world',
    ];

    protected function initializeConfig(): void
    {
        $this->serviceName = "nytimes";
        $this->baseUrl = config('services.nytimes.base_url');
        $this->apiKey = config('services.nytimes.key');
        $this->rateLimit = config('services.nytimes.rate_limit');
        $this->apiKeyParamName = config('services.nytimes.api_key_param');
    }

    /**
     * @param string|null $category
     * @return array
     */
    public function fetchArticles(string $category = null): array
    {
        $nyTimesSection = $category ?
            ($this->categoryMapping[$category] ?? 'all') :
            'all';

        $endpoint = "news/v3/content/nyt/{$nyTimesSection}.json";

        $params = [
            'limit' => 50,
        ];

        $response = $this->makeRequest($endpoint, $params);

        if (!$response || $response['status'] !== 'OK') {
            return [];
        }

        return array_map([$this, 'mapArticle'], $response['results'] ?? []);
    }

    /**
     * @param array $rawArticle
     * @return array
     */
    protected function mapArticle(array $rawArticle): array
    {
        $author = $rawArticle['byline'] ?? $rawArticle['source'] ?? 'The New York Times';
        $author = preg_replace('/^By\s+/i', '', $author);

        $imageUrl = null;
        if (!empty($rawArticle['multimedia'])) {
            $image = collect($rawArticle['multimedia'])
                ->first(fn($media) => $media['type'] === 'image' && $media['subtype'] === 'photo');
            if ($image) {
                $imageUrl = $image['url'];
            }
        }

        return [
            'external_id' => $rawArticle['slug_name'] ?? md5($rawArticle['url'] ?? ''),
            'title' => $rawArticle['title'] ?? '',
            'summary' => $rawArticle['abstract'] ?? '',
            'content' => $rawArticle['abstract'] ?? '',
            'url' => $rawArticle['url'] ?? '',
            'image_url' => $imageUrl,
            'author' => $author ?: 'The New York Times',
            'published_at' => $rawArticle['published_date'] ?? now(),
            'source_metadata' => $rawArticle,
        ];
    }

    public function getSource(): string
    {
        return $this->serviceName;
    }
}
