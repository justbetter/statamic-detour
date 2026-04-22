<?php

namespace JustBetter\Detour\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $key
 * @property ?string $value
 */
class DetourSetting extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];
}
