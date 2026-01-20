<?php

namespace JustBetter\Detour\Tests\Data\File;

use Illuminate\Support\Str;
use JustBetter\Detour\Casts\CommaSeperatedToArray;
use JustBetter\Detour\Models\Detour;
use JustBetter\Detour\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CommaSeperatedToArrayTest extends TestCase
{
    #[Test]
    public function set_returns_value_as_is(): void
    {
        $cast = new CommaSeperatedToArray;
        $model = Detour::create([
            'id' => Str::uuid()->toString(),
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => '::path::',
        ]);

        $result = $cast->set($model, 'tags', 'a,b', []);

        $this->assertSame('a,b', $result);
    }
}
