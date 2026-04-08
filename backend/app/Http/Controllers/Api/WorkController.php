<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Work;
use App\Services\ExternalSourceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class WorkController extends Controller
{
    public function __construct(
        private ExternalSourceService $externalSourceService
    ) {}

    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $type = $request->input('type');
        $page = (int) $request->input('page', 1);
        $perPage = min((int) $request->input('per_page', 50), 100);
        
        $cacheKey = "works:list:{$search}:{$type}:{$page}:{$perPage}";
        
        if ($request->query('all') === 'true') {
            // Return all works with optional search (cached)
            $query = Work::with('categories')->orderBy('title');
            
            if ($search) {
                $query->whereRaw('LOWER(title) LIKE ?', ['%' . strtolower($search) . '%']);
            }
            
            if ($type) {
                $query->where('type', $type);
            }
            
            $works = Cache::remember($cacheKey, 300, function () use ($query, $perPage) {
                return $query->paginate($perPage);
            });
        } else {
            $works = Cache::remember('works:top:10', 600, function () {
                return Work::orderBy('bayesian_rating', 'desc')->limit(10)->get();
            });
        }

        return response()->json($works);
    }

    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100',
            'type' => 'nullable|in:anime,manga,book,comic',
            'source' => 'nullable|in:jikan,anilist,kitsu,tmdb',
        ]);

        $query = $request->input('q');
        $type = $request->input('type', 'anime');
        $source = $request->input('source', 'jikan');

        $results = $this->externalSourceService->searchExternal($query, $type, $source);

        return response()->json([
            'results' => $results,
            'query' => $query,
            'type' => $type,
            'source' => $source,
        ]);
    }

    public function fetchExternal(Request $request)
    {
        $request->validate([
            'external_id' => 'required|string',
            'type' => 'required|in:anime,manga,book,comic',
        ]);

        $details = $this->externalSourceService->fetchDetails(
            $request->input('external_id'),
            $request->input('type')
        );

        if (!$details) {
            return response()->json(['error' => 'Failed to fetch details'], 422);
        }

        return response()->json($details);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:book,manga,anime,comic,hq',
            'canonical_url' => 'nullable|url',
            'external_source_id' => 'nullable|exists:external_sources,id',
            'external_id' => 'nullable|string|max:100',
            'external_url' => 'nullable|url',
            'cover_image_url' => 'nullable|url',
        ]);

        $work = Work::create([
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'canonical_url' => $request->canonical_url,
            'user_id' => auth()->id(),
            'is_user_suggested' => true,
            'external_source_id' => $request->external_source_id,
            'external_id' => $request->external_id,
            'external_url' => $request->external_url,
            'cover_image_url' => $request->cover_image_url,
        ]);

        Cache::forget('works:all');
        Cache::forget('works:top:10');

        return response()->json($work, 201);
    }

    public function show($id)
    {
        $work = Work::with('categories')->findOrFail($id);
        return response()->json($work);
    }

    public function update(Request $request, $id)
    {
        $work = Work::findOrFail($id);

        if ($work->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|required|in:book,manga,anime,comic,hq',
            'canonical_url' => 'nullable|url',
            'external_source_id' => 'nullable|exists:external_sources,id',
            'external_id' => 'nullable|string|max:100',
            'external_url' => 'nullable|url',
            'cover_image_url' => 'nullable|url',
        ]);

            $work->update($request->only([
            'title', 'description', 'type', 'canonical_url',
            'external_source_id', 'external_id', 'external_url', 'cover_image_url'
        ]));

        Cache::forget('works:all');
        Cache::forget('works:top:10');

        return response()->json($work);
    }

    public function sync($id)
    {
        $work = Work::findOrFail($id);
        
        $synced = $this->externalSourceService->syncWorkWithExternal($work);

        if (!$synced) {
            return response()->json(['error' => 'No external source configured'], 422);
        }

        return response()->json($synced);
    }

    public function destroy($id)
    {
        $work = Work::findOrFail($id);

        if ($work->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $work->delete();

        Cache::forget('works:all');
        Cache::forget('works:top:10');

        return response()->json(['message' => 'Work deleted successfully']);
    }

    public function categories()
    {
        $categories = Category::orderBy('name')->get();
        return response()->json($categories);
    }

    public function worksByCategory(Request $request, $categoryId)
    {
        $category = Category::findOrFail($categoryId);
        $works = $category->works()->orderBy('title')->get();
        return response()->json([
            'category' => $category,
            'works' => $works,
        ]);
    }

    public function assignCategories(Request $request, $workId)
    {
        $work = Work::findOrFail($workId);
        
        $request->validate([
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $work->categories()->sync($request->input('category_ids'));
        Cache::forget('works:all');
        
        return response()->json($work->load('categories'));
    }

    public function byLetter(Request $request, $letter)
    {
        $letter = strtoupper($letter);
        $type = $request->input('type');
        
        $query = Work::with('categories')->whereRaw('UPPER(LEFT(title, 1)) = ?', [$letter]);
        
        if ($type) {
            $query->where('type', $type);
        }
        
        $works = $query->orderBy('title')->get();
        
        return response()->json([
            'letter' => $letter,
            'type' => $type,
            'works' => $works,
        ]);
    }

    public function availableLetters(Request $request)
    {
        $type = $request->input('type');
        
        $query = Work::selectRaw('UPPER(LEFT(title, 1)) as letter, COUNT(*) as count');
        
        if ($type) {
            $query->where('type', $type);
        }
        
        $query->groupBy('letter')->orderBy('letter');
        $results = $query->get();
        
        return response()->json($results);
    }
}