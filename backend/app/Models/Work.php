<?php

namespace App\Models;

use App\Services\RankingService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Work extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'type',
        'classification',
        'canonical_url',
        'is_user_suggested',
        'bayesian_rating',
        'user_id',
        'external_source_id',
        'external_id',
        'external_url',
        'cover_image_url',
        'external_references',
    ];

    protected $casts = [
        'bayesian_rating' => 'decimal:2',
    ];

    public function topic(): HasMany
    {
        return $this->HasMany(Topic::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function externalSource(): BelongsTo
    {
        return $this->belongsTo(ExternalSource::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_work');
    }

    public function getBayesianRatingAttribute()
    {
        return $this->attributes['bayesian_rating'] ?? 0;
    }
}
