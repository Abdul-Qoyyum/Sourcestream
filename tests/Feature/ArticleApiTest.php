<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Article;
use App\Models\Source;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ArticleApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_paginated_articles()
    {
        Article::factory()->count(15)->create();

        $response = $this->getJson('/api/articles');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'summary', 'url', 'image_url', 'author', 'published_at']
                ],
                'links',
                'total'
            ])
            ->assertJsonCount(10, 'data');
    }

    public function test_it_filters_articles_by_category()
    {
        $techCategory = Category::factory()->create(['slug' => 'technology']);
        $sportsCategory = Category::factory()->create(['slug' => 'sports']);

        Article::factory()->count(3)->create(['category_id' => $techCategory->id]);
        Article::factory()->count(2)->create(['category_id' => $sportsCategory->id]);

        $response = $this->getJson('/api/articles?category=technology');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_it_searches_articles_by_keyword()
    {
        Article::factory()->create(['title' => 'Laravel development']);
        Article::factory()->create(['title' => 'PHP development']);

        $response = $this->getJson('/api/articles?search=laravel');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['title' => 'Laravel development']);
    }


    public function test_it_filters_articles_by_date_range()
    {
        Article::factory()->create(['published_at' => '2024-01-01 10:00:00']);
        Article::factory()->create(['published_at' => '2024-01-05 10:00:00']);
        Article::factory()->create(['published_at' => '2024-01-10 10:00:00']);

        $response = $this->getJson('/api/articles?from_date=2024-01-01&to_date=2024-01-05');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_it_filters_articles_by_source()
    {
        $source1 = Source::factory()->create(['api_name' => 'newsapi']);
        $source2 = Source::factory()->create(['api_name' => 'opennews']);

        Article::factory()->count(2)->create(['source_id' => $source1->id]);
        Article::factory()->count(3)->create(['source_id' => $source2->id]);

        $response = $this->getJson('/api/articles?sources=newsapi,opennews');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function test_it_returns_article_details()
    {
        $article = Article::factory()->create();

        $response = $this->getJson("/api/articles/{$article->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'title', 'summary', 'content', 'url', 'image_url', 'author', 'published_at']
            ])
            ->assertJsonFragment(['title' => $article->title]);
    }

    public function test_it_returns_available_filters()
    {
        Source::factory()->count(2)->create(['is_active' => true]);
        Source::factory()->create(['is_active' => false]);
        Category::factory()->count(3)->create();

        $response = $this->getJson('/api/articles/filters/get');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'sources' => [['id', 'name', 'api_name']],
                'categories' => [['id', 'name', 'slug']]
            ])
            ->assertJsonCount(2, 'sources')
            ->assertJsonCount(3, 'categories');
    }
}
