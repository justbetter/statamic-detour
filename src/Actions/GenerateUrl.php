<?php

namespace JustBetter\Detour\Actions;

use JustBetter\Detour\Contracts\GeneratesUrl;
use JustBetter\Detour\Data\Detour;
use JustBetter\Detour\Enums\Type;

class GenerateUrl implements GeneratesUrl
{
    public function generate(Detour $detour, string $path): ?string
    {
        if ($detour->isType(Type::Regex)) {
            if (! str_contains($detour->to, '$')) {
                return $detour->to;
            }

            return preg_replace($detour->from, $detour->to, $path);
        }

        return $detour->to;
    }

    public static function bind(): void
    {
        app()->singleton(GeneratesUrl::class, static::class);
    }
}
