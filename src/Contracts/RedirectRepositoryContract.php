<?php

namespace JustBetter\Detour\Contracts;

use JustBetter\Detour\Data\Redirect;

interface RedirectRepositoryContract
{
    /**
     * @return array<int, Redirect>
     */
    public function all(): array;

    public function find(string $id): ?Redirect;

    public function save(Redirect $redirect): void;

    public function delete(Redirect $redirect): void;
}
