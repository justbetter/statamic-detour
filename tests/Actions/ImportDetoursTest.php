<?php

namespace JustBetter\Detour\Tests\Actions;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use JustBetter\Detour\Contracts\ImportsDetour;
use JustBetter\Detour\Contracts\ImportsDetours;
use JustBetter\Detour\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class ImportDetoursTest extends TestCase
{
    #[Test]
    public function it_can_import_detours(): void
    {
        $action = app(ImportsDetours::class);
        $disk = config()->string('justbetter.statamic-detour.actions.disk');
        Storage::fake($disk);
        Bus::fake();

        $this->mock(ImportsDetour::class, function (MockInterface $mock) {
            $mock->shouldReceive('import')->twice();
        });

        $csv = <<<'CSV'
            id,from,to,type,code,sites  
            075d6161-2a33-4d21-9768-87e09a73c925,/test,/to,path,302,default
            175d6161-2a33-4d21-9768-87e09a73c925,/test-2,/to-2,path,302,default
            CSV;

        $path = 'import.csv';
        Storage::disk($disk)->put($path, $csv);

        $action->import($path);
    }
}
