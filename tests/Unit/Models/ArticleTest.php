<?php

namespace Tests\Unit\Models;

use App\Models\Article;
use App\Models\Category;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_should_create_an_article()
    {
        $source = Source::factory()->create();
        $category = Category::factory()->create();

        $article = Article::factory()->create([
            'source_id' => $source->id,
            'category_id' => $category->id,
        ]);

        $this->assertInstanceOf(Article::class, $article);
        $this->assertEquals($source->id, $article->source_id);
        $this->assertEquals($category->id, $article->category_id);
    }

    public function test_it_belongs_to_source()
    {
        $source = Source::factory()->create();
        $article = Article::factory()->create(['source_id' => $source->id]);

        $this->assertInstanceOf(Source::class, $article->source);
        $this->assertEquals($source->id, $article->source->id);
    }


    public function test_it_belongs_to_category()
    {
        $category = Category::factory()->create();
        $article = Article::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(Category::class, $article->category);
        $this->assertEquals($category->id, $article->category->id);
    }
}
