<?php

namespace App\Services;

use App\Models\Topic;
use App\Models\Work;
use Illuminate\Support\Collection;

class SearchService
{
    private const MAX_RESULTS = 20;

    public function search(string $query, ?string $type = null): array
    {
        if (strlen($query) < 2) {
            return [
                'works' => [],
                'topics' => [],
                'total' => 0,
            ];
        }

        $searchQuery = $this->prepareQuery($query);

        $works = collect();
        $topics = collect();

        if (!$type || $type === 'works') {
            $works = $this->searchWorks($searchQuery);
        }

        if (!$type || $type === 'topics') {
            $topics = $this->searchTopics($searchQuery);
        }

        return [
            'works' => $works,
            'topics' => $topics,
            'total' => $works->count() + $topics->count(),
        ];
    }

    private function searchWorks(string $query): Collection
    {
        return Work::with('user:id,name')
            ->where(function ($q) use ($query) {
                $q->whereRaw('LOWER(title) LIKE ?', ["%{$query}%"])
                  ->orWhereRaw('LOWER(description) LIKE ?', ["%{$query}%"]);
            })
            ->orderByDesc('bayesian_rating')
            ->limit(self::MAX_RESULTS)
            ->get()
            ->map(fn($work) => [
                'id' => $work->id,
                'title' => $work->title,
                'type' => $work->type,
                'description' => $work->description,
                'bayesian_rating' => $work->bayesian_rating,
                'user' => $work->user ? [
                    'id' => $work->user->id,
                    'name' => $work->user->name,
                ] : null,
            ]);
    }

    private function searchTopics(string $query): Collection
    {
        return Topic::with(['user:id,name', 'work:id,title'])
            ->where(function ($q) use ($query) {
                $q->whereRaw('LOWER(title) LIKE ?', ["%{$query}%"])
                  ->orWhereRaw('LOWER(content) LIKE ?', ["%{$query}%"]);
            })
            ->orderByDesc('created_at')
            ->limit(self::MAX_RESULTS)
            ->get()
            ->map(fn($topic) => [
                'id' => $topic->id,
                'title' => $topic->title,
                'content' => mb_substr($topic->content, 0, 200),
                'rating' => $topic->rating,
                'user' => $topic->user ? [
                    'id' => $topic->user->id,
                    'name' => $topic->user->name,
                ] : null,
                'work' => $topic->work ? [
                    'id' => $topic->work->id,
                    'title' => $topic->work->title,
                ] : null,
                'created_at' => $topic->created_at,
            ]);
    }

    private function prepareQuery(string $query): string
    {
        return mb_strtolower(trim($query));
    }
}
