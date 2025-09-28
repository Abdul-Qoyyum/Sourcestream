<?php

namespace Tests\Unit\Models;

use App\Models\Article;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_a_source()
    {
        $source = Source::factory()->create([
            'name' => 'NewsAPI',
            'api_name' => 'newsapi',
            'base_url' => 'https://newsapi.org/v2/',
        ]);

        $this->assertInstanceOf(Source::class, $source);
        $this->assertEquals('NewsAPI', $source->name);
        $this->assertEquals('newsapi', $source->api_name);
    }

    public function test_it_has_articles()
    {
        $source = Source::factory()->create();
        Article::factory()->count(3)->create(['source_id' => $source->id]);

        $this->assertCount(3, $source->articles);
        $this->assertInstanceOf(Article::class, $source->articles->first());
    }

    public function test_it_can_be_inactive()
    {
        $source = Source::factory()->inactive()->create();

        $this->assertFalse($source->is_active);
    }

    public function test_it_can_have_configuration()
    {
        $config = ['supports_authors' => true, 'categories' => ['tech', 'sports']];
        $source = Source::factory()->create(['config' => $config]);

        $this->assertEquals($config, $source->config);
        $this->assertTrue($source->config['supports_authors']);
    }
}
