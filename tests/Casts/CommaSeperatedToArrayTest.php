<?php

namespace JustBetter\Detour\Tests\Data\File;

use JustBetter\Detour\Casts\CommaSeperatedToArray;
use JustBetter\Detour\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CommaSeperatedToArrayTest extends TestCase
{
    #[Test]
    public function set_returns_value_as_is(): void
    {
        $cast = new CommaSeperatedToArray;

        $result = $cast->set(null, 'tags', ['a', 'b'], []);

        $this->assertSame(['a', 'b'], $result);
    }
}
