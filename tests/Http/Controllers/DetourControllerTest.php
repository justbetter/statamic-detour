<?php

namespace JustBetter\Detour\Tests\Http\Controllers;

use Illuminate\View\View;
use JustBetter\Detour\Actions\ResolveRepository;
use JustBetter\Detour\Contracts\ListsDetours;
use JustBetter\Detour\Data\Form;
use JustBetter\Detour\Http\Controllers\DetourController;
use JustBetter\Detour\Http\Requests\IndexRequest;
use JustBetter\Detour\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;

class DetourControllerTest extends TestCase
{
    #[Test]
    public function it_can_load_a_view(): void
    {
        /** @var \Statamic\Auth\File\User $user */
        $user = User::make();
        $user->id('test-user')->email('test@example.com')->makeSuper();

        $this->actingAs($user);

        $controller = app(DetourController::class);
        $contract = app(ListsDetours::class);
        $request = IndexRequest::create('/cp/detours', 'GET');
        $request->setContainer(app())->setRedirector(app('redirect'));
        $request->validateResolved();

        $this->assertInstanceOf(View::class, $controller->index($request, $contract));
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
            'query_string_handling' => 'use_global',
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
            'to' => '/::to::',
            'code' => '302',
            'type' => 'regex',
            'sites' => [],
        ]);

        $response2->assertUnprocessable();
        $response2->assertJsonValidationErrors(['from']);
        $response2->assertJsonMissingValidationErrors(['to']);

        $response3 = $this->withoutMiddleware()->postJson(cp_route('justbetter.detours.store'), [
            'from' => '/::from::',
            'to' => '/::to::',
            'code' => '302',
            'type' => 'path',
            'sites' => [],
            'query_string_handling' => 'strip_specific_keys',
            'query_string_strip_keys' => null,
        ]);

        $response3->assertUnprocessable();
        $response3->assertJsonValidationErrors(['query_string_strip_keys']);
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
