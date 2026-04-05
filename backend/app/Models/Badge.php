<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'category',
        'points',
        'criteria',
    ];

    protected $casts = [
        'criteria' => 'array',
        'points' => 'integer',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_badges')
            ->withPivot('earned_at')
            ->withTimestamps();
    }

    public static function getDefaultBadges(): array
    {
        return [
            [
                'name' => 'Primeiro Passo',
                'slug' => 'first-topic',
                'description' => 'Criou seu primeiro tópico',
                'icon' => '📝',
                'category' => 'engagement',
                'points' => 10,
                'criteria' => ['type' => 'topics_count', 'value' => 1],
            ],
            [
                'name' => 'Leitor Ativo',
                'slug' => 'active-reader',
                'description' => 'Criou 10 tópicos',
                'icon' => '📚',
                'category' => 'engagement',
                'points' => 50,
                'criteria' => ['type' => 'topics_count', 'value' => 10],
            ],
            [
                'name' => 'Comentador',
                'slug' => 'first-comment',
                'description' => 'Fez seu primeiro comentário',
                'icon' => '💬',
                'category' => 'engagement',
                'points' => 5,
                'criteria' => ['type' => 'interactions_count', 'value' => 1],
            ],
            [
                'name' => 'Conversador',
                'slug' => 'active-commenter',
                'description' => 'Fez 20 comentários',
                'icon' => '🗣️',
                'category' => 'engagement',
                'points' => 40,
                'criteria' => ['type' => 'interactions_count', 'value' => 20],
            ],
            [
                'name' => 'Contribuidor',
                'slug' => 'content-creator',
                'description' => 'Criou 5 obras',
                'icon' => '🎨',
                'category' => 'creation',
                'points' => 100,
                'criteria' => ['type' => 'works_count', 'value' => 5],
            ],
            [
                'name' => 'Avaliador',
                'slug' => 'first-rating',
                'description' => 'Fez sua primeira avaliação',
                'icon' => '⭐',
                'category' => 'rating',
                'points' => 10,
                'criteria' => ['type' => 'ratings_count', 'value' => 1],
            ],
            [
                'name' => 'Crítico',
                'slug' => 'critic',
                'description' => 'Fez 50 avaliações',
                'icon' => '🎭',
                'category' => 'rating',
                'points' => 100,
                'criteria' => ['type' => 'ratings_count', 'value' => 50],
            ],
        ];
    }
}
