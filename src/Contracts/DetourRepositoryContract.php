<?php

namespace JustBetter\Detour\Contracts;

interface DetourRepositoryContract
{
    /**
     * @return array<string, DetourContract>
     */
    public function all(): array;

    public function find(string $id): ?DetourContract;

    public function save(DetourContract $detour): void;

    public function delete(DetourContract $detour): void;
}
