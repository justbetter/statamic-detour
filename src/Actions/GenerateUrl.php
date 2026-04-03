<?php

namespace JustBetter\Detour\Actions;

use JustBetter\Detour\Contracts\GeneratesUrl;
use JustBetter\Detour\Data\Detour;
use JustBetter\Detour\Enums\QueryStringHandling;
use JustBetter\Detour\Enums\Type;
use JustBetter\Detour\Support\DetourSettings;

class GenerateUrl implements GeneratesUrl
{
    public function __construct(protected DetourSettings $settings) {}

    public function generate(Detour $detour, string $path, array $queryParameters = []): ?string
    {
        $url = null;

        if ($detour->isType(Type::Regex)) {
            if (! str_contains($detour->to, '$')) {
                $url = $detour->to;
            } else {
                $url = preg_replace($detour->from, $detour->to, $path);
            }
        } else {
            $url = $detour->to;
        }

        if (! is_string($url)) {
            return null;
        }

        return $this->appendQueryString($url, $detour, $queryParameters);
    }

    /**
     * @param  array<string, mixed>  $queryParameters
     */
    protected function appendQueryString(string $url, Detour $detour, array $queryParameters): string
    {
        $rawHandling = (string) $detour->get('query_string_handling');

        $handling = ($rawHandling === '' || $rawHandling === 'use_global')
            ? null
            : QueryStringHandling::tryFrom($rawHandling);

        $handling = $handling
            ?? $this->settings->defaultQueryStringHandling();

        $queryParameters = match ($handling) {
            QueryStringHandling::PassThrough => $queryParameters,
            QueryStringHandling::StripCompletely => [],
            QueryStringHandling::StripSpecificKeys => $this->stripSpecificKeys($queryParameters, $detour),
        };

        if ($queryParameters === []) {
            return $url;
        }

        $separator = str_contains($url, '?') ? '&' : '?';

        return $url.$separator.http_build_query($queryParameters);
    }

    /**
     * @param  array<string, mixed>  $queryParameters
     * @return array<string, mixed>
     */
    protected function stripSpecificKeys(array $queryParameters, Detour $detour): array
    {
        $rawKeys = $detour->get('query_string_strip_keys');

        if (! is_string($rawKeys) || trim($rawKeys) === '') {
            $rawKeys = $this->settings->defaultQueryStringStripKeys();
        }

        $keys = str((string) $rawKeys)
            ->explode(',')
            ->map(fn (string $key): string => trim($key))
            ->filter()
            ->values()
            ->all();

        if ($keys === []) {
            return $queryParameters;
        }

        return collect($queryParameters)
            ->reject(fn (mixed $value, string $key): bool => in_array($key, $keys, true))
            ->all();
    }

    public static function bind(): void
    {
        app()->singleton(GeneratesUrl::class, static::class);
    }
}
