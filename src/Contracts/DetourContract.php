<?php

namespace JustBetter\Detour\Contracts;

interface DetourContract
{
    public function id(): string;

    public static function make(?string $id = null): self;

    public function data(?array $data = null): array|static;

    public function get(string $key, $default = null): mixed;

    public function jsonSerialize(): array;
}