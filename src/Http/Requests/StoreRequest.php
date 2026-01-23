<?php

namespace JustBetter\Detour\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property string $from
 * @property string $to
 * @property string $type
 * @property int $code
 * @property array<int, string> $sites
 */
class StoreRequest extends FormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'from' => [
                'present',
                'string',
                Rule::when($this->input('type') === 'path', ['starts_with:/']),
            ],
            'to' => 'present|string|starts_with:/',
            'type' => 'required|string|in:path,regex',
            'code' => 'required|integer',
            'sites' => 'present|array',
        ];
    }
}
