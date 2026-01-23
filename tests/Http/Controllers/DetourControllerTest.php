<?php

namespace JustBetter\Detour\Tests\Http\Controllers;

use Illuminate\View\View;
use JustBetter\Detour\Actions\ResolveRepository;
use JustBetter\Detour\Contracts\ListsDetours;
use JustBetter\Detour\Data\Form;
use JustBetter\Detour\Http\Controllers\DetourController;
use JustBetter\Detour\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DetourControllerTest extends TestCase
{
    #[Test]
    public function it_can_load_a_view(): void
    {
        $controller = app(DetourController::class);
        $contract = app(ListsDetours::class);

        $this->assertInstanceOf(View::class, $controller->index($contract));
    }

    #[Test]
    public function it_can_store_data(): void
    {
        $response = $this->withoutMiddleware()->postJson(cp_route('justbetter.detours.store'), [
            'from' => '/::from::',
            'to' => '/::to::',
            'code' => '302',
            'type' => 'path',
            'sites' => [],
        ]);

        $response->assertOk();
    }

    #[Test]
    public function it_can_not_store_invalid_detours(): void
    {
        $response = $this->withoutMiddleware()->postJson(cp_route('justbetter.detours.store'), [
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => 'path',
            'sites' => [],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['from']);
        $response->assertJsonValidationErrors(['to']);

        $response2 = $this->withoutMiddleware()->postJson(cp_route('justbetter.detours.store'), [
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => 'regex',
            'sites' => [],
        ]);

        $response->assertUnprocessable();
        $response2->assertJsonMissingValidationErrors(['from']);
        $response->assertJsonValidationErrors(['to']);
    }

    #[Test]
    public function it_can_destroy_data(): void
    {
        $contract = app(ResolveRepository::class);
        $repository = $contract->resolve();
        $data = [
            'from' => '/::from::',
            'to' => '/::to::',
            'code' => '302',
            'type' => 'path',
            'sites' => [],
        ];

        $form = Form::make($data);

        $detour = $repository->store($form);

        $response = $this->withoutMiddleware()->deleteJson(cp_route('justbetter.detours.destroy', $detour->get('id')));

        $response->assertOk();
    }
}
