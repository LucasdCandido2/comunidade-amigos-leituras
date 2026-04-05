<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExternalSource extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'url', 'type'];

    public const TYPE_WIKI = 'wiki';
    public const TYPE_SITE = 'site';
    public const TYPE_FORUM = 'forum';
    public const TYPE_STORE = 'store';

    public static function types(): array
    {
        return [
            self::TYPE_WIKI,
            self::TYPE_SITE,
            self::TYPE_FORUM,
            self::TYPE_STORE,
        ];
    }

    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            self::TYPE_WIKI => '📖',
            self::TYPE_SITE => '🌐',
            self::TYPE_FORUM => '💬',
            self::TYPE_STORE => '🛒',
            default => '🔗',
        };
    }
}
