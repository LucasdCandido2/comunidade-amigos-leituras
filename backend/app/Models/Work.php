<?php

namespace App\Models;

use App\Services\RankingService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Work extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'type',
        'canonical_url',
        'is_user_suggested',
        'bayesian_rating',
        'user_id'
    ];

    protected $casts = [
        'bayesian_rating' => 'decimal:2',
    ];

    public function topic(): HasMany
    {
        return $this->HasMany(Topic::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getBayesianRatingAttribute()
    {
        // Return stored rating without recalculation in getter
        // Recalculation should be done explicitly via controller or scheduled job
        return $this->attributes['bayesian_rating'] ?? 0;
    }
}
