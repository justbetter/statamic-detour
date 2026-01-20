<?php

namespace JustBetter\Detour\Data;

use Illuminate\Support\Fluent;

abstract class BaseDetour extends Fluent
{
    abstract public function id();
}
