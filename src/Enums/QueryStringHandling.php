<?php

namespace JustBetter\Detour\Enums;

enum QueryStringHandling: string
{
    case PassThrough = 'pass_through';
    case StripCompletely = 'strip_completely';
    case StripSpecificKeys = 'strip_specific_keys';
}
