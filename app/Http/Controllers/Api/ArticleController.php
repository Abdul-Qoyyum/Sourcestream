<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Services\ArticleService;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Info(
 *     title="SourceStream API",
 *     version="1.0.0"
 * )
 * @OA\PathItem(path="/api/articles")
 */
class ArticleController extends Controller
{
    use ApiResponseTrait;

    /**
     * @OA\Get(
     *     path="/api/articles",
     *     summary="Get list of articles",
     *     tags={"Articles"},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="search by title, summary, content or author",
     *         @OA\Schema(type="string", example="Game")
     *      ),
     *      @OA\Parameter(
     *          name="category",
     *          in="query",
     *          required=false,
     *          description="search by category",
     *          @OA\Schema(
     *             type="string",
     *             enum={"business", "entertainment", "general", "health", "science", "sports", "technology"},
     *             example="technology"
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
     *           @OA\Schema(type="string", example="2025-09-26 07:28:50")
     *       ),
     *      @OA\Parameter(
     *           name="to_date",
     *           in="query",
     *           required=false,
     *           description="published_at end date",
     *           @OA\Schema(type="string", example="2025-09-26 12:03:42")
     *       ),
     *       @OA\Parameter(
     *          name="page",
     *          in="query",
     *          required=false,
     *          description="Current page",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          in="query",
     *          required=false,
     *          description="Per page",
     *          @OA\Schema(type="integer")
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="List of Articles"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $response = ArticleService::index($request);
        return $this->successResponse($response);
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
    public function show($id): JsonResponse
    {
        $response = ArticleService::show($id);
        if(!$response){
            return $this->errorResponse('Article not found',
                null,
                Response::HTTP_NOT_FOUND);
        }
        return $this->successResponse($response);
    }

    /**
     * @OA\Get(
     *     path="/api/articles/filters/get",
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
        return $this->successResponse($response);
    }


}
