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
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->get(route('admin.languages.index'));

        $response->assertForbidden();
    }

    public function test_admin_user_can_view_the_languages_screen(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.languages.index'));

        $response->assertOk();
    }
}
