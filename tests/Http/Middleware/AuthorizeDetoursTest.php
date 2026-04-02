<?php

namespace JustBetter\Detour\Tests\Http\Middleware;

use JustBetter\Detour\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Role;
use Statamic\Facades\User;

class AuthorizeDetoursTest extends TestCase
{
    #[Test]
    public function it_forbids_users_without_the_detours_permission(): void
    {
        $role = Role::make('cp-only')->permissions(['access cp']);
        $role->save();

        /** @var \Statamic\Auth\File\User $user */
        $user = User::make();
        $user
            ->id('test-user-no-detours')
            ->email('no-detours@example.com')
            ->assignRole($role)
            ->save();

        $this->actingAs($user);

        $this->get(cp_route('index'))->assertRedirect(cp_route('dashboard'));
        $this->get(cp_route('justbetter.detours.index'))->assertForbidden();
    }

    #[Test]
    public function it_can_authorize_detours(): void
    {
        $permission = config()->string('justbetter.statamic-detour.permissions.access');

        $role = Role::make('detours-access')->permissions(['access cp', $permission]);
        $role->save();

        /** @var \Statamic\Auth\File\User $user */
        $user = User::make();
        $user
            ->id('test-user-with-detours')
            ->email('with-detours@example.com')
            ->assignRole($role)
            ->save();

        $this->actingAs($user);

        $this->get(cp_route('index'))->assertRedirect(cp_route('dashboard'));
        $this->get(cp_route('justbetter.detours.index'))->assertOk();
    }
}
