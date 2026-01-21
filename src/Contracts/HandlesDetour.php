<?php

namespace JustBetter\Detour\Contracts;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

interface HandlesDetour
{
    public function resolveRedirect(Request $request): ?RedirectResponse;
}
