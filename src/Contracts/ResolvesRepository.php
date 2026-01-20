<?php

namespace JustBetter\Detour\Contracts;

use JustBetter\Detour\Repositories\BaseRepository;

interface ResolvesRepository
{
    public function resolve(): BaseRepository;
}
