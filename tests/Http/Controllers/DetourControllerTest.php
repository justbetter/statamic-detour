<?php

namespace JustBetter\Detour\Tests\Http\Controllers;

use Illuminate\View\View;
use JustBetter\Detour\Contracts\DetourRepositoryContract;
use JustBetter\Detour\Data\File\Detour;
use JustBetter\Detour\Http\Controllers\DetourController;
use JustBetter\Detour\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DetourControllerTest extends TestCase
{
    #[Test]
    public function it_can_load_a_view(): void
    {
        $controller = app(DetourController::class);
        $contract = app(DetourRepositoryContract::class);
        $this->assertInstanceOf(View::class, $controller->index($contract));
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
        $repository = app(DetourRepositoryContract::class);

        $detour = Detour::make();

        $data = [
            'id' => $detour->id(),
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => '::path::',
        ];

        $detour->data($data);

        $repository->save($detour);

        $response = $this->withoutMiddleware()->deleteJson(cp_route('justbetter.detours.destroy', $detour->id()));

        $response->assertOk();
    }
}
