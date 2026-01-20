<?php

namespace JustBetter\Detour\Tests\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\View\View;
use JustBetter\Detour\Actions\ResolveRepository;
use JustBetter\Detour\Data\FileDetour;
use JustBetter\Detour\Http\Controllers\DetourController;
use JustBetter\Detour\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DetourControllerTest extends TestCase
{
    #[Test]
    public function it_can_load_a_view(): void
    {
        $controller = app(DetourController::class);

        $this->assertInstanceOf(View::class, $controller->index());
    }

    #[Test]
    public function it_can_store_data(): void
    {
        $response = $this->withoutMiddleware()->postJson(cp_route('justbetter.detours.store'), [
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => '::path::',
        ]);

        $response->assertOk();
    }

    #[Test]
    public function it_can_destroy_data(): void
    {
        $contract = app(ResolveRepository::class);
        $repository = $contract->resolve();
        $data = [
            'id' => Str::uuid()->toString(),
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => '::path::',
        ];

        $detour = FileDetour::make($data);

        $repository->save($detour);

        $response = $this->withoutMiddleware()->deleteJson(cp_route('justbetter.detours.destroy', $detour->id()));

        $response->assertOk();
    }
}
