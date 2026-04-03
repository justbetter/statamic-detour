<?php

namespace JustBetter\Detour\Tests\Actions;

use JustBetter\Detour\Actions\GenerateUrl;
use JustBetter\Detour\Contracts\GeneratesUrl;
use JustBetter\Detour\Data\Detour;
use JustBetter\Detour\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class GenerateUrlTest extends TestCase
{
    #[Test]
    public function it_returns_null_when_regex_replace_fails(): void
    {
        $action = app(GenerateUrl::class);

        $detour = Detour::make([
            'from' => '/(/',
            'to' => '/target/$1',
            'type' => 'regex',
            'code' => 301,
            'sites' => [],
        ]);

        set_error_handler(static fn (): bool => true);
        $result = $action->generate($detour, '/source');
        restore_error_handler();

        $this->assertNull($result);
    }

    #[Test]
    public function it_can_bind_the_generate_url_contract(): void
    {
        GenerateUrl::bind();

        $resolved = app(GeneratesUrl::class);

        $this->assertInstanceOf(GenerateUrl::class, $resolved);
    }
}
