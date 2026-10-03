<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.languages.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_non_admin_user_is_forbidden(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('admin.languages.index'));

        $response->assertForbidden();
    }

    public function test_admin_user_can_view_the_languages_screen(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.languages.index'));

        $response->assertOk();
    }

    public function test_non_admin_user_cannot_change_the_learning_structure(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->post(route('admin.languages.store'), [
            'name' => 'Should not be created',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('languages', ['name' => 'Should not be created']);
    }

    public function test_admin_dashboard_from_main_is_still_reachable_for_admins(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk();
    }
}
