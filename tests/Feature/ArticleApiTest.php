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
        Article::factory()->count(2)->create();

        $response = $this->getJson('/api/articles');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'articles' => [
                        '*' => [
                            'id',
                            'source_id',
                            'category_id',
                            'external_id',
                            'title',
                            'summary',
                            'content',
                            'url',
                            'image_url',
                            'author',
                            'published_at',
                            'source_metadata',
                            'created_at',
                            'updated_at',
                            'source' => [
                                'id',
                                'name',
                                'api_name',
                                'base_url',
                                'is_active',
                                'rate_limit',
                                'config',
                                'created_at',
                                'updated_at',
                            ],
                            'category' => [
                                'id',
                                'name',
                                'slug',
                                'created_at',
                                'updated_at',
                            ]
                        ]
                    ],
                    'meta' => [
                        'current_page',
                        'per_page',
                        'from',
                        'to',
                        'total',
                        'last_page'
                    ]
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Success'
            ])
            ->assertJsonCount(2, 'data.articles');
    }

    public function test_it_filters_articles_by_category()
    {
        $techCategory = Category::factory()->create(['slug' => 'technology']);
        $sportsCategory = Category::factory()->create(['slug' => 'sports']);

        Article::factory()->count(3)->create(['category_id' => $techCategory->id]);
        Article::factory()->count(2)->create(['category_id' => $sportsCategory->id]);

        $response = $this->getJson('/api/articles?category=technology');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data.articles');
    }

    public function test_it_returns_paginated_articles_with_meta_data()
    {
        Article::factory()->count(15)->create();

        $response = $this->getJson('/api/articles?page=2&per_page=5');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Success'
            ])
            ->assertJsonCount(5, 'data.articles')
            ->assertJsonPath('data.meta.current_page', 2)
            ->assertJsonPath('data.meta.per_page', 5)
            ->assertJsonPath('data.meta.total', 15)
            ->assertJsonPath('data.meta.last_page', 3)
            ->assertJsonPath('data.meta.from', 6)
            ->assertJsonPath('data.meta.to', 10);
    }

    public function test_it_searches_articles_by_keyword()
    {
        Article::factory()->create(['title' => 'Laravel development']);
        Article::factory()->create(['title' => 'PHP development']);

        $response = $this->getJson('/api/articles?search=laravel');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.articles')
            ->assertJsonFragment(['title' => 'Laravel development']);
    }


    public function test_it_filters_articles_by_date_range()
    {
        Article::factory()->create(['published_at' => '2024-01-01 10:00:00']);
        Article::factory()->create(['published_at' => '2024-01-05 10:00:00']);
        Article::factory()->create(['published_at' => '2024-01-10 10:00:00']);

        $response = $this->getJson('/api/articles?from_date=2024-01-01&to_date=2024-01-05');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.articles');
    }

    public function test_it_filters_articles_by_source()
    {
        $source1 = Source::factory()->create(['api_name' => 'newsapi']);
        $source2 = Source::factory()->create(['api_name' => 'opennews']);

        Article::factory()->count(2)->create(['source_id' => $source1->id]);
        Article::factory()->count(3)->create(['source_id' => $source2->id]);

        $response = $this->getJson('/api/articles?sources=newsapi,opennews');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data.articles');
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
                'data' => [
                    'sources' => [['id', 'name', 'api_name']],
                    'categories' => [['id', 'name', 'slug']]
                ]
            ])
            ->assertJsonCount(2, 'data.sources')
            ->assertJsonCount(3, 'data.categories');
    }

    public function test_it_returns_empty_articles_array_when_no_results()
    {
        $response = $this->getJson('/api/articles?search=nonexistent');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Success',
                'data' => [
                    'articles' => [],
                    'meta' => [
                        'current_page' => 1,
                        'per_page' => 10,
                        'from' => 0,
                        'to' => 0,
                        'total' => 0,
                        'last_page' => 0
                    ]
                ]
            ]);
    }

    public function test_it_respects_per_page_limits()
    {
        Article::factory()->count(25)->create();

        $response1 = $this->getJson('/api/articles');
        $response1->assertJsonPath('data.meta.per_page', 10)
            ->assertJsonCount(10, 'data.articles');

        $response2 = $this->getJson('/api/articles?per_page=5');
        $response2->assertJsonPath('data.meta.per_page', 5)
            ->assertJsonCount(5, 'data.articles');

        $response3 = $this->getJson('/api/articles?per_page=20');
        $response3->assertJsonPath('data.meta.per_page', 20)
            ->assertJsonCount(20, 'data.articles');
    }
}
