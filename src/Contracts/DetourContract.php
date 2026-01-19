<?php

namespace JustBetter\Detour\Contracts;

interface DetourContract
{
    public function id(): string;

    public static function make(?string $id = null): self;

    /**
     * @param  array<string, string | array<int, string>>  $data
     * @return array<string, string | array<int, string>> | static
     */
    public function data(?array $data = null): array|static;

    /**
     * @return array<string, string | array<int, string>>
     */
    public function jsonSerialize(): array;
}
