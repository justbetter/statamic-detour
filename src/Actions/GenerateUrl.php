<?php

namespace JustBetter\Detour\Actions;

use JustBetter\Detour\Contracts\GeneratesUrl;
use JustBetter\Detour\Data\Detour;
use JustBetter\Detour\Enums\Type;

class GenerateUrl implements GeneratesUrl
{
    public function generate(Detour $detour, string $path): string
    {
        if ($detour->isType(Type::Regex)) {
            preg_match($detour->from, $path, $matches);

            $url = $detour->to;

            foreach ($matches as $index => $match) {
                $url = str_replace('$'.$index, $match, $url);
            }

            return $url;
        }

        return $detour->to;
    }

    public static function bind(): void
    {
        app()->singleton(GeneratesUrl::class, static::class);
    }
}
