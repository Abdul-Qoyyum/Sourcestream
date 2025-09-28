<?php

namespace App\Http\Services;

use App\Http\Contracts\NewsServiceInterface;

class NewsAPIService extends BaseNewsService implements NewsServiceInterface
{
    protected function initializeConfig(): void
    {
        $this->serviceName = "newsapi";
        $this->baseUrl = config('services.newsapi.base_url');
        $this->apiKey = config('services.newsapi.key');
        $this->rateLimit = config('services.newsapi.rate_limit');
    }

    public function fetchArticles(string $category = null): array
    {
        $params = [
            'language' => 'en',
            'pageSize' => 50,
        ];

        if ($category) {
            $params['category'] = $category;
        }

        $response = $this->makeRequest('top-headlines', $params);

        if (!$response || $response['status'] !== 'ok') {
            return [];
        }

        return array_map([$this, 'mapArticle'], $response['articles'] ?? []);
    }

    protected function mapArticle(array $rawArticle): array
    {
        return [
            'external_id' => !empty($rawArticle['source']['id']) ? md5($rawArticle['source']['id']) : null,
            'title' => $rawArticle['title'] ?? '',
            'summary' => $rawArticle['description'] ?? '',
            'content' => $rawArticle['content'] ?? '',
            'url' => $rawArticle['url'] ?? '',
            'image_url' => $rawArticle['urlToImage'] ?? null,
            'author' => $rawArticle['author'] ?? null,
            'published_at' => $rawArticle['publishedAt'] ?? now(),
            'source_metadata' => $rawArticle,
        ];
    }

    public function getSource(): string
    {
        return $this->serviceName;
    }
}
