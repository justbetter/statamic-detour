<?php

namespace JustBetter\Detour\Tests\Actions;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use JustBetter\Detour\Actions\ResolveRepository;
use JustBetter\Detour\Contracts\ImportsDetour;
use JustBetter\Detour\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ImportDetourTest extends TestCase
{
    #[Test]
    public function it_can_import_detours(): void
    {
        $resolver = app(ResolveRepository::class);
        $repository = $resolver->resolve();
        $action = app(ImportsDetour::class);
        $disk = config()->string('justbetter.statamic-detour.actions.disk');
        Storage::fake($disk);
        Bus::fake();

        $data = [
            'id' => '075d6161-2a33-4d21-9768-87e09a73c925',
            'from' => '/test',
            'to' => '/to',
            'type' => 'path',
            'code' => 302,
            'sites' => 'default',
        ];
        $action->import($data);

        $this->assertCount(1, $repository->get());
    }

    #[Test]
    public function it_can_store_import_detours(): void
    {
        $resolver = app(ResolveRepository::class);
        $repository = $resolver->resolve();
        $action = app(ImportsDetour::class);
        $disk = config()->string('justbetter.statamic-detour.actions.disk');
        Storage::fake($disk);
        Bus::fake();

        $data = [
            'from' => '/test',
            'to' => '/to',
            'type' => 'path',
            'code' => 302,
            'sites' => 'default',
        ];
        $action->import($data);

        $this->assertCount(1, $repository->get());
    }

    #[Test]
    public function it_skips_importing_detours_with_invalid_values(): void
    {
        $resolver = app(ResolveRepository::class);
        $repository = $resolver->resolve();
        $action = app(ImportsDetour::class);
        $disk = config()->string('justbetter.statamic-detour.actions.disk');
        Storage::fake($disk);
        Bus::fake();

        $invalidCodeValue = [
            'from' => '/test',
            'to' => '/to',
            'type' => 'path',
            'code' => 309,
            'sites' => 'default',
        ];
        $action->import($invalidCodeValue);

        $invalidCodeType = [
            'from' => '/test',
            'to' => '/to',
            'type' => 'path',
            'code' => 'string',
            'sites' => 'default',
        ];
        $action->import($invalidCodeType);

        $invalidPathValue = [
            'from' => 'test',
            'to' => 'to',
            'type' => 'path',
            'code' => 302,
            'sites' => 'default',
        ];
        $action->import($invalidPathValue);

        $invalidTypeValue = [
            'from' => '/test',
            'to' => '/to',
            'type' => 'paths',
            'code' => 302,
            'sites' => 'default',
        ];
        $action->import($invalidTypeValue);

        $this->assertEmpty($repository->get());
    }
}
