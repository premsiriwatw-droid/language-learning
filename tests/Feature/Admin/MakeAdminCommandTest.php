<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class MakeAdminCommandTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_command_grants_and_revokes_admin(): void
    {
        $user = User::factory()->create(['email' => 'teacher@example.com']);

        $this->artisan('app:make-admin', ['email' => 'teacher@example.com'])->assertSuccessful();
        $this->assertTrue($user->fresh()->is_admin);

        $this->artisan('app:make-admin', ['email' => 'teacher@example.com', '--revoke' => true])->assertSuccessful();
        $this->assertFalse($user->fresh()->is_admin);
    }

    public function test_command_fails_for_unknown_email(): void
    {
        $this->artisan('app:make-admin', ['email' => 'nobody@example.com'])->assertFailed();
    }

    public function test_is_admin_cannot_be_set_through_registration(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Sneaky',
            'email' => 'sneaky@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_admin' => 1,
        ]);

        $user = User::where('email', 'sneaky@example.com')->first();

        if ($user) {
            $this->assertFalse($user->is_admin);
        } else {
            $this->assertDatabaseMissing('users', ['is_admin' => true]);
        }
    }
}
