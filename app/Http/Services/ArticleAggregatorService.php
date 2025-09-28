<?php

namespace App\Http\Services;

use App\Models\Article;
use App\Models\Source;
use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ArticleAggregatorService
{
    private Collection $newsServices;

    public function __construct(array $services = null)
    {
        if ($services) {
            // (for testing)
            $this->newsServices = collect($services);
        } else {
            //(for production)
            $this->newsServices = collect([
                app(NewsAPIService::class),
                app(GuardianService::class),
                app(NYTimesService::class),
            ]);
        }
    }

    /**
     * @param string|null $categorySlug
     * @return bool
     */
    public function aggregate(string $categorySlug = null): bool
    {
        $category = $categorySlug ? Category::query()->where('slug', $categorySlug)->first() : null;
        $successCount = 0;
        $totalSources = $this->newsServices->count();

        foreach ($this->newsServices as $service) {
            try {
                $source = Source::query()->where('api_name', $service->getSource())->first();

                if (!$source || !$source->is_active) {
                    Log::info("Skipping inactive source: {$service->getSource()}");
                    continue;
                }

                $articles = $service->fetchArticles($category?->name);

                if (!empty($articles)) {
                    $this->storeArticles($articles, $source, $category);
                    $successCount++;
                    Log::info("Successfully fetched " . count($articles) . " articles from {$source->name}");
                }
            } catch (\Exception $e) {
                Log::error("Failed to fetch articles from {$service->getSource()}: {$e->getMessage()}");
            }
        }
        Log::info("Article aggregation completed. Successful sources: {$successCount}/{$totalSources}");
        return $successCount > 0;
    }

    /**
     * @param array $articles
     * @param Source $source
     * @param Category|null $category
     * @return void
     */
    private function storeArticles(array $articles, Source $source, ?Category $category): void
    {
        $storedCount = 0;
        $errorCount = 0;

        foreach ($articles as $articleData) {
            try {
                Article::query()->updateOrCreate(
                    [
                        'source_id' => $source->id,
                        'external_id' => $articleData['external_id'],
                    ],
                    array_merge($articleData, [
                        'source_id' => $source->id,
                        'category_id' => $category?->id,
                        'author' => $this->sanitizeAuthor($articleData['author']),
                    ])
                );
                $storedCount++;

            } catch (\Exception $e) {
                Log::error("Failed to store article: {$e->getMessage()}", [
                    'source' => $source->api_name,
                    'title' => $articleData['title'] ?? 'Unknown'
                ]);
                $errorCount++;
            }
        }

        Log::info("Stored {$storedCount} articles from {$source->name}. Errors: {$errorCount}");
    }

    /**
     * @param string|null $author
     * @return string|null
     */
    private function sanitizeAuthor(?string $author): ?string
    {
        if (!$author) {
            return null;
        }

        $author = trim($author);

        $author = preg_replace('/^(By|By\s+)/i', '', $author);
        $author = trim($author);

        if (strlen($author) > 100) {
            $author = substr($author, 0, 97) . '...';
        }

        return $author ?: null;
    }
}
