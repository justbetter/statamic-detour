<?php

namespace JustBetter\Detour\Tests\Http\Middleware;

use JustBetter\Detour\Http\Middleware\RedirectIfNeeded;
use JustBetter\Detour\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RedirectIfNeededTest extends TestCase
{
    #[Test]
    public function it_executes_middleware(): void
    {
        $this->markTestSkipped();
        // make a redirect

        // go to redirect from
        $response = $this->get('/');

        // expect to be redirected
        $response->middleware(RedirectIfNeeded::class)->assertStatus(200);
    }
}
