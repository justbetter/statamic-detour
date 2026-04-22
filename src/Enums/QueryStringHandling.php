<?php

namespace JustBetter\Detour\Enums;

enum QueryStringHandling: string
{
    case PassThrough = 'pass_through';
    case StripCompletely = 'strip_completely';
    case StripSpecificKeys = 'strip_specific_keys';

    public function label(): string
    {
        return match ($this) {
            self::PassThrough => __('Pass through'),
            self::StripCompletely => __('Strip completely'),
            self::StripSpecificKeys => __('Strip specific keys'),
        };
    }
}
