<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Services\ArticleAggregatorService;
use Symfony\Component\Console\Command\Command as CommandAlias;

class AggregateArticles extends Command
{
    protected $signature = 'articles:aggregate {category?}';
    protected $description = 'Aggregate articles from all news sources';

    public function handle(ArticleAggregatorService $aggregator): int
    {
        $category = $this->argument('category');

        $this->info("Starting article aggregation..." . ($category ? " Category: {$category}" : ''));

        $success = $aggregator->aggregate($category);

        if ($success) {
            $this->info('Articles aggregated successfully!');
            return CommandAlias::SUCCESS;
        }

        $this->error('Failed to aggregate articles from any source!');
        return CommandAlias::FAILURE;
    }
}
