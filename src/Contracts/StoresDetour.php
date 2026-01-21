<?php

namespace JustBetter\Detour\Contracts;

use JustBetter\Detour\Data\Detour;
use JustBetter\Detour\Data\Form;

interface StoresDetour
{
    public function store(Form $form): Detour;
}
