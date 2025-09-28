<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ArticleController;


Route::prefix('articles')->group(function () {
    Route::get('/', [ArticleController::class, 'index']);
    Route::get('/{article}', [ArticleController::class, 'show']);
    Route::get('/filters/get', [ArticleController::class, 'getFilters']);
});
