<?php
namespace App\Http\Services;

use App\Models\Article;
use App\Models\Category;
use App\Models\Source;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ArticleService
{

    public static function index(Request $request): LengthAwarePaginator
    {        $query = Article::with(['source', 'category'])
        ->orderBy('published_at', 'desc');

        // Search by keyword
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('summary', 'like', "%{$searchTerm}%")
                    ->orWhere('content', 'like', "%{$searchTerm}%");
            });
        }

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Filter by sources
        if ($request->has('sources') && $request->sources) {
            $sources = explode(',', $request->sources);
            $query->whereHas('source', function ($q) use ($sources) {
                $q->whereIn('api_name', $sources);
            });
        }

        if ($request->has('from_date') && $request->from_date) {
            $query->where('published_at', '>=', Carbon::parse($request->from_date)->startOfDay());
        }

        if ($request->has('to_date') && $request->to_date) {
            $query->where('published_at', '<=', Carbon::parse($request->to_date)->endOfDay());
        }

        return $query->paginate($request->get('per_page', 10));
    }

    /**
     * @return array
     */
    public static function getFilters(): array
    {
        $sources = Source::query()->where('is_active', true)->get(['id', 'name', 'api_name']);
        $categories = Category::all(['id', 'name', 'slug']);
        return [
            'sources' => $sources,
            'categories' => $categories,
        ];
    }


    /**
     * @param Article $article
     * @return Article[]
     */
    public static function show(Article $article): array
    {
        $article->load(['source', 'category']);
        return [
            'data' => $article
        ];
    }
}
