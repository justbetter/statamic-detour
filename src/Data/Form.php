<?php

namespace JustBetter\Detour\Data;

use Illuminate\Validation\Rules\Enum;
use JustBetter\Detour\Enums\Type;

/**
 * @property ?string $id
 * @property string $from
 * @property string $to
 * @property string $type
 * @property int $code
 * @property array<int, string> $sites
 * @property string $query_string_handling
 * @property string $query_string_strip_keys
 *
 * @extends Data<string, mixed>
 */
class Form extends Data
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->rules = [
            'id' => 'sometimes|unique:detours,id',
            'from' => 'required|string|starts_with:/',
            'to' => 'required|string|starts_with:/',
            'type' => ['required', new Enum(Type::class)],
            'code' => 'required|integer|in:301,302,307,308',
            'sites' => 'sometimes|array',
            'sites.*' => 'string',
            'query_string_handling' => 'sometimes|string|in:use_global,pass_through,strip_completely,strip_specific_keys',
            'query_string_strip_keys' => 'sometimes|string',
        ];
    }
}
