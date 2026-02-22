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

        $csv = <<<'CSV'
            id,from,to,type,code,sites  
            075d6161-2a33-4d21-9768-87e09a73c925,/test,/to,path,302,default
            175d6161-2a33-4d21-9768-87e09a73c925,/test-2,/to-2,path,302,default
            CSV;

        $file = UploadedFile::fake()->createWithContent(
            'detours.csv',
            $csv
        );

        $response = $this->withoutMiddleware()->post(cp_route('justbetter.detours.actions.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('statamic.cp.justbetter.detours.actions.index'));

        Queue::assertPushed(ImportDetours::class);
    }

    #[Test]
    public function it_does_not_import_invalid_files(): void
    {
        $disk = config()->string('justbetter.statamic-detour.actions.disk');

        Storage::fake($disk);

        Queue::fake();

        $file = UploadedFile::fake()->create(
            name: 'detours.xlsx',
            kilobytes: 5,
            mimeType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        $response = $this->withoutMiddleware()->post(cp_route('justbetter.detours.actions.import'), [
            'file' => $file,
        ]);

        $response->assertSessionHasErrors();

        Queue::assertNotPushed(ImportDetours::class);
    }

    #[Test]
    public function it_does_not_import_invalid_csv_files_with_missing_headers(): void
    {
        $disk = config()->string('justbetter.statamic-detour.actions.disk');

        Storage::fake($disk);
        Queue::fake();

        $csv = <<<'CSV'
          from,to,type,sites,
          /test,/to,path,default
        CSV;

        $file = UploadedFile::fake()->createWithContent('detours.csv', $csv);

        $response = $this
            ->withoutMiddleware()
            ->from(cp_route('justbetter.detours.actions.index'))
            ->post(cp_route('justbetter.detours.actions.import'), [
                'file' => $file,
            ]);

        $response->assertSessionHasErrors();

        Queue::assertNotPushed(ImportDetours::class);
    }

    #[Test]
    public function it_does_not_import_csv_with_no_content(): void
    {
        $disk = config()->string('justbetter.statamic-detour.actions.disk');

        Storage::fake($disk);
        Queue::fake();

        $file = UploadedFile::fake()->createWithContent('detours.csv', '');

        $response = $this
            ->withoutMiddleware()
            ->from(cp_route('justbetter.detours.actions.index'))
            ->post(cp_route('justbetter.detours.actions.import'), [
                'file' => $file,
            ]);

        $response->assertSessionHasErrors();

        Queue::assertNotPushed(ImportDetours::class);
    }
}
