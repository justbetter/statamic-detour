<?php

namespace JustBetter\Detour\Data;

/**
 * @property int $size
 * @property int $page
 * @property ?string $search
 *
 * @extends Data<string, mixed>
 */
class Paginate extends Data
{
    protected array $rules = [
        'size' => 'required|integer|min:1',
        'page' => 'required|integer|min:1',
        'search' => 'nullable|string',
    ];
}
