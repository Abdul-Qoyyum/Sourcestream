<?php

use App\Http\Services\ArticleAggregatorService;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ArticleController;

Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/{article}', [ArticleController::class, 'show']);
Route::get('/filters', [ArticleController::class, 'getFilters']);

Route::post('/admin/aggregate', function () {
    app(ArticleAggregatorService::class)->aggregate();
    return response()->json(['message' => 'Articles aggregated successfully']);
});
