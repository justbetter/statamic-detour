<?php

namespace JustBetter\Detour\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use JustBetter\Detour\Database\Factories\DetourFactory;

/**
 * @property string $id
 * @property string $from
 * @property string $to
 * @property string $type
 * @property int $code
 * @property array<int, string> $sites
 */
class Detour extends Model
{
    /** @use HasFactory<DetourFactory> */
    use HasFactory;

    use HasUuids;

    protected $guarded = [];

    protected $keyType = 'string';

    public function casts(): array
    {
        return [
            'sites' => 'array',
        ];
    }

    protected static function newFactory(): DetourFactory
    {
        return DetourFactory::new();
    }
}
