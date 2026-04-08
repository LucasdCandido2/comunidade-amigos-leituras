<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExternalSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'type',
        'api_endpoint',
        'api_key',
        'media_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public const TYPE_WIKI = 'wiki';
    public const TYPE_SITE = 'site';
    public const TYPE_FORUM = 'forum';
    public const TYPE_STORE = 'store';
    public const TYPE_API = 'api';

    public const MEDIA_ANIME = 'anime';
    public const MEDIA_MANGA = 'manga';
    public const MEDIA_COMIC = 'comic';
    public const MEDIA_MOVIE = 'movie';
    public const MEDIA_SERIES = 'series';
    public const MEDIA_BOOK = 'book';

    public static function types(): array
    {
        return [
            self::TYPE_WIKI,
            self::TYPE_SITE,
            self::TYPE_FORUM,
            self::TYPE_STORE,
            self::TYPE_API,
        ];
    }

    public static function mediaTypes(): array
    {
        return [
            self::MEDIA_BOOK,
            self::MEDIA_ANIME,
            self::MEDIA_MANGA,
            self::MEDIA_COMIC,
            self::MEDIA_MOVIE,
            self::MEDIA_SERIES,
        ];
    }

    public function works(): HasMany
    {
        return $this->hasMany(Work::class);
    }

    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            self::TYPE_WIKI => '📖',
            self::TYPE_SITE => '🌐',
            self::TYPE_FORUM => '💬',
            self::TYPE_STORE => '🛒',
            self::TYPE_API => '🔌',
            default => '🔗',
        };
    }

    public function getMediaTypeIconAttribute(): string
    {
        return match($this->media_type) {
            self::MEDIA_BOOK => '📚',
            self::MEDIA_ANIME => '🎌',
            self::MEDIA_MANGA => '🈶',
            self::MEDIA_COMIC => '💥',
            self::MEDIA_MOVIE => '🎬',
            self::MEDIA_SERIES => '📺',
            default => '📄',
        };
    }
}
