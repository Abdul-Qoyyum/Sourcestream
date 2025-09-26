<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Article;
use App\Models\Source;
use App\Models\Category;

class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        return [
            'source_id' => Source::factory(),
            'category_id' => Category::factory(),
            'external_id' => $this->faker->unique()->uuid(),
            'title' => $this->faker->sentence(),
            'summary' => $this->faker->paragraph(),
            'content' => $this->faker->text(500),
            'url' => $this->faker->url(),
            'image_url' => $this->faker->imageUrl(),
            'author' => $this->faker->name(),
            'published_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'source_metadata' => [
                'original_source' => $this->faker->word(),
                'api_version' => 'v1',
            ],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
