<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Source;
use Illuminate\Database\Seeder;

class SourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Source::query()->updateOrCreate([
            'api_name' => 'newsapi',
        ],[
            'name' => 'NewsAPI',
            'base_url' => config('services.newsapi.base_url'),
            'is_active' => true,
            'rate_limit' => 100,
        ]);

        Source::query()->updateOrCreate([
            'api_name' => 'guardian',
        ],[
            'name' => 'The Guardian',
            'base_url' => config('services.guardian.base_url'),
            'is_active' => true,
            'rate_limit' => 60,
        ]);

        Source::query()->updateOrCreate([
            'api_name' => 'nytimes',
        ],[
            'name' => 'New York Times',
            'base_url' => config('services.nytimes.base_url'),
            'is_active' => true,
            'rate_limit' => 50,
        ]);

        $categories = [
            'business', 'entertainment', 'general', 'health',
            'science', 'sports', 'technology'
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate([
                'name' => ucfirst($category),
            ],[
                'slug' => $category,
            ]);
        }
    }
}
