<?php

namespace JustBetter\Detour\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Statamic\Facades\Site;

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
                Rule::when($this->input('type') === 'regex', [
                    function (string $attribute, mixed $value, \Closure $fail): void {
                        if (! is_string($value) || @preg_match($value, '') === false) {
                            $fail('The '.$attribute.' must be a valid regex.');
                        }
                    },
                ]),
            ],
            'to' => 'present|string|starts_with:/',
            'type' => 'required|string|in:path,regex',
            'code' => 'required|integer',
            'sites' => 'present|array',
        ];
    }
}
