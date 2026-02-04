<?php

namespace JustBetter\Detour\Tests\Http\Controllers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use JustBetter\Detour\Contracts\ExportsDetours;
use JustBetter\Detour\Http\Controllers\ImportExportController;
use JustBetter\Detour\Jobs\ImportDetours;
use JustBetter\Detour\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class ImportExportControllerTest extends TestCase
{
    #[Test]
    public function it_can_load_a_view(): void
    {
        $controller = app(ImportExportController::class);
        $this->assertInstanceOf(View::class, $controller->index());
    }

    #[Test]
    public function it_can_export_detours(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'export');

        $this->mock(ExportsDetours::class, function (MockInterface $mock) use ($path) {
            $mock->shouldReceive('export')->once()->andReturn($path);
        });

        $controller = app(ImportExportController::class);
        $controller->export(app(ExportsDetours::class));
    }

    #[Test]
    public function it_can_import_detours(): void
    {
        $disk = config()->string('justbetter.statamic-detour.actions.disk');

        Storage::fake($disk);

        Queue::fake();

        $file = UploadedFile::fake()->create(
            name: 'detours.csv',
            kilobytes: 5,
            mimeType: 'text/csv'
        );

        $response = $this->withoutMiddleware()->post(cp_route('justbetter.detours.actions.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('statamic.cp.justbetter.detours.actions.index'));

        Queue::assertPushed(ImportDetours::class);
    }
}
