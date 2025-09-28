<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Services\ArticleService;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Info(
 *     title="SourceStream API",
 *     version="1.0.0"
 * )
 * @OA\PathItem(path="/api/articles")
 */
class ArticleController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/articles",
     *     summary="Get list of articles",
     *     tags={"Articles"},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="search by title, summary or content",
     *         @OA\Schema(type="string", example="podcast")
     *      ),
     *      @OA\Parameter(
     *          name="category",
     *          in="query",
     *          required=false,
     *          description="search by category",
     *          @OA\Schema(
     *             type="string",
     *             enum={"business", "entertainment", "general", "health", "science", "sports", "technology"},
     *             example="business"
     *          )
     *      ),
     *      @OA\Parameter(
     *           name="source",
     *           in="query",
     *           required=false,
     *           description="search by source",
     *           @OA\Schema(type="string", example="newsapi,guardian,nytimes")
     *      ),
     *      @OA\Parameter(
     *           name="from_date",
     *           in="query",
     *           required=false,
     *           description="published_at start date",
     *           @OA\Schema(type="string", example="2024-01-01 10:00:00")
     *       ),
     *      @OA\Parameter(
     *           name="to_date",
     *           in="query",
     *           required=false,
     *           description="published_at end date",
     *           @OA\Schema(type="string", example="2024-01-10 10:00:00")
     *       ),
     *     @OA\Response(
     *         response=200,
     *         description="List of Articles"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $response = ArticleService::index($request);
        return response()->json($response);
    }

    /**
     * @OA\Get(
     *     path="/api/articles/{article}",
     *     summary="Get an Article by ID",
     *     tags={"Articles"},
     *     @OA\Parameter(
     *         name="article",
     *         in="path",
     *         required=true,
     *         description="Article ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Article Data"
     *     )
     * )
     */
    public function show(Article $article): JsonResponse
    {
        $response = ArticleService::show($article);
        return response()->json($response);
    }

    /**
     * @OA\Get(
     *     path="/api/filters",
     *     summary="Get the filter option for Articles",
     *     tags={"Articles"},
     *     @OA\Response(
     *       response=200,
     *       description="Articles Filter"
     *     )
     * ),
     */
    public function getFilters(): JsonResponse
    {
        $response = ArticleService::getFilters();
        return response()->json($response);
    }


}
