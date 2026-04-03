<?php

namespace JustBetter\Detour\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SettingsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'query_string_default_handling' => 'required|string|in:pass_through,strip_completely,strip_specific_keys',
            'query_string_default_strip_keys' => [
                'nullable',
                'string',
                Rule::requiredIf($this->input('query_string_default_handling') === 'strip_specific_keys'),
            ],
        ];
    }
}
