<?php
namespace App\Http\Services;

use App\Models\Article;
use App\Models\Category;
use App\Models\Source;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ArticleService
{

    public static function index(Request $request): array
    {        $query = Article::with(['source', 'category'])
        ->orderBy('published_at', 'desc');


        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('summary', 'like', "%{$searchTerm}%")
                    ->orWhere('content', 'like', "%{$searchTerm}%")
                    ->orWhere('author', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->has('category') && $request->category) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

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

        $total = $query->count();

        $page = max(1, (int) $request->get('page', 1));
        $perPage = min(50, max(1, (int) $request->get('per_page', 10)));
        $skip = ($page - 1) * $perPage;

        $articles = $query->skip($skip)->take($perPage)->get();

        $lastPage = ceil($total / $perPage);

        return [
            'articles' => $articles,
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'from' => $total > 0 ? $skip + 1 : 0,
                'to' => $total > 0 ? min($skip + $perPage, $total) : 0,
                'total' => $total,
                'last_page' => $lastPage,
            ]
        ];
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
     * @param $id
     * @return Article|null
     */
    public static function show($id):?Article
    {
        return Article::with(['source', 'category'])
            ->where('id',$id)
            ->first();
    }
}
