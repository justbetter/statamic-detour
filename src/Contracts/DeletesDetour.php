<?php

namespace JustBetter\Detour\Contracts;

interface DeletesDetour
{
    public function delete(string $id): void;
}
