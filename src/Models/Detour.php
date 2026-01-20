<?php

namespace JustBetter\Detour\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

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
    use HasUuids;

    protected $guarded = [];

    protected $keyType = 'string';

    public function casts(): array
    {
        return [
            'sites' => 'array',
        ];
    }
}
