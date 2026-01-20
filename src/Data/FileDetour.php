<?php

namespace JustBetter\Detour\Data;

use Illuminate\Support\Str;
use JustBetter\Detour\Contracts\DetourContract;

class FileDetour extends BaseDetour implements DetourContract
{
    public function id(): string
    {
        /** @var string $id */
        $id = $this->data('id') ?? Str::uuid()->toString();

        return $id;
    }
}
