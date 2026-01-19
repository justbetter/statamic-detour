<?php

namespace JustBetter\Detour\Models;

use Illuminate\Database\Eloquent\Model;
use JustBetter\Detour\Casts\CommaSeperatedToArray;

class Detour extends Model
{
    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    public function casts(): array
    {
        return [
            'sites' => CommaSeperatedToArray::class,
        ];
    }
}
