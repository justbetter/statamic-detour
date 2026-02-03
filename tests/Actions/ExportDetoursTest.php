<?php

namespace JustBetter\Detour\Tests\Actions;

use Illuminate\Support\Facades\Storage;
use JustBetter\Detour\Contracts\ExportsDetours;
use JustBetter\Detour\Contracts\ResolvesRepository;
use JustBetter\Detour\Data\Form;
use JustBetter\Detour\Enums\Type;
use JustBetter\Detour\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ExportDetoursTest extends TestCase
{
    #[Test]
    public function it_can_export_detours(): void
    {
        $action = app(ExportsDetours::class);
        $repository = app(ResolvesRepository::class)->resolve();
        $disk = config()->string('justbetter.statamic-detour.actions.disk');
        Storage::fake($disk);

        $data = Form::make([
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'sites' => [
                '::site::',
            ],
            'type' => Type::Path,
        ]);

        $repository->store($data);

        $action->export();

        Storage::disk($disk)->assertExists('export.csv');
    }
}
