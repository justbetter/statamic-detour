<?php

namespace JustBetter\Detour\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use JustBetter\Detour\Enums\Type;

/**
 * @property string $id
 * @property string $from
 * @property string $to
 * @property Type $type
 * @property int $code
 * @property array<int, string> $sites
 */
class Detour extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $keyType = 'string';

    public function casts(): array
    {
        return [
            'type' => Type::class,
            'sites' => 'array',
        ];
    }
}
