<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Category;
use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_a_category()
    {
        $category = Category::factory()->create([
            'name' => 'Technology',
            'slug' => 'technology',
        ]);

        $this->assertInstanceOf(Category::class, $category);
        $this->assertEquals('Technology', $category->name);
        $this->assertEquals('technology', $category->slug);
    }

    public function test_it_has_articles()
    {
        $category = Category::factory()->create();
        Article::factory()->count(2)->create(['category_id' => $category->id]);

        $this->assertCount(2, $category->articles);
        $this->assertInstanceOf(Article::class, $category->articles->first());
    }

    public function test_it_automatically_slugs_the_name()
    {
        $category = Category::factory()->create(['name' => 'Business News']);

        $this->assertEquals('business-news', $category->slug);
    }
}
