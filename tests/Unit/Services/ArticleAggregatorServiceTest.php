<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Http\Services\ArticleAggregatorService;
use App\Http\Contracts\NewsServiceInterface;
use App\Models\Source;
use App\Models\Category;
use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class ArticleAggregatorServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_aggregates_articles_from_all_three_sources()
    {
        Source::factory()->create(['api_name' => 'newsapi']);
        Source::factory()->create(['api_name' => 'guardian']);
        Source::factory()->create(['api_name' => 'nytimes']);
        Category::factory()->create(['name' => 'Technology', 'slug' => 'technology']);

        $mockService1 = Mockery::mock(NewsServiceInterface::class);
        $mockService1->shouldReceive('getSource')->andReturn('newsapi');
        $mockService1->shouldReceive('fetchArticles')
            ->with(Mockery::any())
            ->andReturn([$this->createArticleData('newsapi', 'NewsAPI Article')]);

        $mockService2 = Mockery::mock(NewsServiceInterface::class);
        $mockService2->shouldReceive('getSource')->andReturn('guardian');
        $mockService2->shouldReceive('fetchArticles')
            ->with(Mockery::any())
            ->andReturn([$this->createArticleData('guardian', 'Guardian Article')]);

        $mockService3 = Mockery::mock(NewsServiceInterface::class);
        $mockService3->shouldReceive('getSource')->andReturn('nytimes');
        $mockService3->shouldReceive('fetchArticles')
            ->with(Mockery::any())
            ->andReturn([$this->createArticleData('nytimes', 'NYTimes Article')]);

        $aggregator = new ArticleAggregatorService([
            $mockService1,
            $mockService2,
            $mockService3,
        ]);

        $result = $aggregator->aggregate('technology');

        $this->assertTrue($result);
        $this->assertEquals(3, Article::query()->count());

        $titles = Article::query()->pluck('title')->toArray();
        $this->assertContains('NewsAPI Article', $titles);
        $this->assertContains('Guardian Article', $titles);
        $this->assertContains('NYTimes Article', $titles);
    }

    public function test_it_passes_correct_category_to_services()
    {
        Source::factory()->create(['api_name' => 'newsapi']);
        Category::factory()->create(['name' => 'Sports', 'slug' => 'sports']);

        $mockService = Mockery::mock(NewsServiceInterface::class);
        $mockService->shouldReceive('getSource')->andReturn('newsapi');
        $mockService->shouldReceive('fetchArticles')
            ->with('Sports')
            ->andReturn([$this->createArticleData('newsapi', 'Sports Article')]);

        $aggregator = new ArticleAggregatorService([$mockService]);
        $result = $aggregator->aggregate('sports');

        $this->assertTrue($result);
        $this->assertEquals(1, Article::query()->count());
        $this->assertEquals('Sports Article', Article::first()->title);
    }

    public function test_it_handles_null_category()
    {
        Source::factory()->create(['api_name' => 'newsapi']);

        $mockService = Mockery::mock(NewsServiceInterface::class);
        $mockService->shouldReceive('getSource')->andReturn('newsapi');
        $mockService->shouldReceive('fetchArticles')
            ->with(null)
            ->andReturn([$this->createArticleData('newsapi', 'General Article')]);

        $aggregator = new ArticleAggregatorService([$mockService]);
        $result = $aggregator->aggregate();

        $this->assertTrue($result);
        $this->assertEquals(1, Article::count());
        $this->assertEquals('General Article', Article::query()->first()->title);
    }

    public function test_it_handles_unknown_category_gracefully()
    {
        Source::factory()->create(['api_name' => 'newsapi']);

        $mockService = Mockery::mock(NewsServiceInterface::class);
        $mockService->shouldReceive('getSource')->andReturn('newsapi');
        $mockService->shouldReceive('fetchArticles')
            ->with(null)
            ->andReturn([$this->createArticleData('newsapi', 'General Article')]);

        $aggregator = new ArticleAggregatorService([$mockService]);
        $result = $aggregator->aggregate('unknown-category');

        $this->assertTrue($result);
        $this->assertEquals(1, Article::query()->count());
    }

    private function createArticleData(string $source, string $title): array
    {
        return [
            'external_id' => 'test-' . $source . '-' . uniqid(),
            'title' => $title,
            'summary' => 'Test Summary from ' . $source,
            'content' => 'Test Content from ' . $source,
            'url' => 'https://example.com/article-' . $source,
            'image_url' => 'https://example.com/image.jpg',
            'author' => 'Test Author from ' . $source,
            'published_at' => now()->toISOString(),
            'source_metadata' => [],
        ];
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
