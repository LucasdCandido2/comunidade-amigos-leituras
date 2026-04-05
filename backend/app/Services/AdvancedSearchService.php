<?php

namespace App\Services;

use App\Models\Topic;
use App\Models\Work;
use Illuminate\Support\Collection;

class AdvancedSearchService
{
    public function search(string $query, ?string $type = null, array $options = []): array
    {
        if (strlen($query) < 2) {
            return [
                'works' => [],
                'topics' => [],
                'total' => 0,
                'query' => $query,
                'filters' => [],
            ];
        }

        $works = collect();
        $topics = collect();

        $highlightEnabled = $options['highlight'] ?? true;
        $filters = $options['filters'] ?? [];
        $sortBy = $options['sort_by'] ?? 'relevance';

        if (!$type || $type === 'works') {
            $works = $this->searchWorks($query, $highlightEnabled, $filters['work_type'] ?? null);
        }

        if (!$type || $type === 'topics') {
            $topics = $this->searchTopics($query, $highlightEnabled, $sortBy);
        }

        return [
            'works' => $works,
            'topics' => $topics,
            'total' => $works->count() + $topics->count(),
            'query' => $query,
            'filters' => $this->getAvailableFilters($query),
        ];
    }

    private function searchWorks(string $query, bool $highlight = true, ?string $typeFilter = null): Collection
    {
        $queryLower = mb_strtolower(trim($query));

        $worksQuery = Work::with('user:id,name')
            ->where(function ($q) use ($queryLower) {
                $q->whereRaw('LOWER(title) LIKE ?', ["%{$queryLower}%"])
                  ->orWhereRaw('LOWER(description) LIKE ?', ["%{$queryLower}%"]);
            });

        if ($typeFilter) {
            $worksQuery->where('type', $typeFilter);
        }

        $works = $worksQuery
            ->orderByDesc('bayesian_rating')
            ->limit(20)
            ->get()
            ->map(function ($work) use ($query, $highlight) {
                $result = [
                    'id' => $work->id,
                    'title' => $work->title,
                    'type' => $work->type,
                    'description' => $work->description,
                    'bayesian_rating' => $work->bayesian_rating,
                    'user' => $work->user ? [
                        'id' => $work->user->id,
                        'name' => $work->user->name,
                    ] : null,
                ];

                if ($highlight) {
                    $result['title_highlighted'] = $this->highlight($work->title, $query);
                    $result['description_highlighted'] = $this->highlight($work->description ?? '', $query);
                }

                return $result;
            });

        return $works;
    }

    private function searchTopics(string $query, bool $highlight = true, string $sortBy = 'relevance'): Collection
    {
        $queryLower = mb_strtolower(trim($query));

        $topicsQuery = Topic::with(['user:id,name', 'work:id,title'])
            ->where(function ($q) use ($queryLower) {
                $q->whereRaw('LOWER(title) LIKE ?', ["%{$queryLower}%"])
                  ->orWhereRaw('LOWER(content) LIKE ?', ["%{$queryLower}%"]);
            })
            ->whereNull('deleted_at');

        $topics = $topicsQuery
            ->limit(30)
            ->get()
            ->map(function ($topic) use ($query, $highlight) {
                $result = [
                    'id' => $topic->id,
                    'title' => $topic->title,
                    'content' => mb_substr(strip_tags($topic->content), 0, 200),
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
                    'relevance_score' => $this->calculateRelevance($topic, $query),
                ];

                if ($highlight) {
                    $result['title_highlighted'] = $this->highlight($topic->title, $query);
                    $result['content_snippet'] = $this->getContentSnippet($topic->content, $query, 150);
                }

                return $result;
            });

        if ($sortBy === 'relevance') {
            return $topics->sortByDesc('relevance_score')->values();
        }

        return $topics->sortByDesc('created_at')->values();
    }

    private function calculateRelevance($topic, string $query): int
    {
        $score = 0;
        $queryLower = mb_strtolower($query);
        $titleLower = mb_strtolower($topic->title);
        $contentLower = mb_strtolower(strip_tags($topic->content ?? ''));

        if (str_contains($titleLower, $queryLower)) {
            $score += 10;
        }

        if (str_starts_with($titleLower, $queryLower)) {
            $score += 5;
        }

        if (str_contains($contentLower, $queryLower)) {
            $score += 5;
        }

        $count = substr_count($contentLower, $queryLower);
        $score += min($count, 3);

        return $score;
    }

    private function highlight(?string $text, string $query): ?string
    {
        if (!$text) return null;

        $pattern = '/(' . preg_quote($query, '/') . ')/ui';
        return preg_replace($pattern, '<mark>$1</mark>', $text);
    }

    private function getContentSnippet(string $content, string $query, int $maxLength = 150): string
    {
        $content = strip_tags($content);
        $queryLower = mb_strtolower($query);
        $contentLower = mb_strtolower($content);

        $pos = strpos($contentLower, $queryLower);

        if ($pos === false) {
            return mb_substr($content, 0, $maxLength) . (mb_strlen($content) > $maxLength ? '...' : '');
        }

        $start = max(0, $pos - 50);
        $snippet = mb_substr($content, $start, $maxLength);

        if ($start > 0) {
            $snippet = '...' . $snippet;
        }
        if ($start + $maxLength < mb_strlen($content)) {
            $snippet .= '...';
        }

        return $this->highlight($snippet, $query);
    }

    private function getAvailableFilters(string $query): array
    {
        return [
            'work_types' => [
                ['value' => 'book', 'label' => 'Livros', 'count' => Work::where('type', 'book')->count()],
                ['value' => 'manga', 'label' => 'Mangás', 'count' => Work::where('type', 'manga')->count()],
                ['value' => 'anime', 'label' => 'Animes', 'count' => Work::where('type', 'anime')->count()],
                ['value' => 'comic', 'label' => 'Quadrinhos', 'count' => Work::where('type', 'comic')->count()],
            ],
            'sort_options' => [
                ['value' => 'relevance', 'label' => 'Relevância'],
                ['value' => 'recent', 'label' => 'Mais recentes'],
            ],
        ];
    }
}
