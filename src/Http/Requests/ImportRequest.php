<?php

namespace JustBetter\Detour\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Validator;
use Spatie\SimpleExcel\SimpleExcelReader;

/**
 * @property UploadedFile $file
 */
class ImportRequest extends FormRequest
{
    /** @var array<int, string> */
    protected array $requiredHeaders = ['from', 'to', 'type', 'code'];

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => 'required|mimetypes:text/csv',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        if ($validator->fails()) {
            return;
        }

        $validator->after(function (Validator $validator): void {
            $file = $this->file('file');

            $headers = SimpleExcelReader::create(
                $file->getRealPath(),
                'csv'
            )->getHeaders();

            if (! $headers) {
                $validator->errors()->add('file', __('The CSV file does not contain a valid header row.'));

                return;
            }

            $headers = collect($headers)
                ->map(function ($header) {
                    $value = is_string($header) ? trim($header) : '';

                    return preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;
                })
                ->all();

            $missingHeaders = collect($this->requiredHeaders)
                ->diff($headers)
                ->values()
                ->all();

            if ($missingHeaders !== []) {
                $validator->errors()->add(
                    'file',
                    __('Missing required CSV header(s): :headers', [
                        'headers' => implode(', ', $missingHeaders),
                    ])
                );
            }
        });
    }
}
