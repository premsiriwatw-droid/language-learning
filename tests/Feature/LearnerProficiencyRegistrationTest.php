<?php

namespace Tests\Feature;

use App\Enums\LearnerProficiency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LearnerProficiencyRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_shows_all_proficiency_choices(): void
    {
        $this->get(route('register'))->assertOk()
            ->assertSee('Beginner')->assertSee('Intermediate')->assertSee('Advanced')->assertSee('Not sure');
    }

    #[DataProvider('proficiencies')]
    public function test_selected_proficiency_is_saved_without_changing_authentication(string $value): void
    {
        $this->post(route('register.store'), $this->registration(['learner_proficiency' => $value]))
            ->assertRedirect('/languages');

        $user = User::firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame(LearnerProficiency::from($value), $user->learner_proficiency);
        $this->assertFalse($user->placement_required);
        $this->assertSame('user', $user->role);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public static function proficiencies(): array
    {
        return [['beginner'], ['intermediate'], ['advanced']];
    }

    public function test_not_sure_leaves_proficiency_pending_and_redirects_to_placement(): void
    {
        $this->post(route('register.store'), $this->registration(['learner_proficiency' => 'not_sure']))
            ->assertRedirect(route('placement.show'));

        $user = User::firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertNull($user->learner_proficiency);
        $this->assertTrue($user->placement_required);
        $this->assertSame('user', $user->role);
    }

    public function test_old_registration_payload_defaults_safely_to_beginner(): void
    {
        $this->post(route('register.store'), $this->registration())->assertRedirect('/languages');
        $user = User::firstOrFail();
        $this->assertSame(LearnerProficiency::Beginner, $user->learner_proficiency);
        $this->assertFalse($user->placement_required);
    }

    #[DataProvider('invalidProficiencies')]
    public function test_invalid_proficiency_is_rejected(mixed $value): void
    {
        $this->post(route('register.store'), $this->registration(['learner_proficiency' => $value]))
            ->assertSessionHasErrors('learner_proficiency');
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public static function invalidProficiencies(): array
    {
        return [['expert'], ['Beginner'], [''], [null], [['advanced']]];
    }

    public function test_registration_cannot_inject_role_or_reward_fields(): void
    {
        $this->post(route('register.store'), $this->registration([
            'learner_proficiency' => 'beginner',
            'role' => 'admin',
            'placement_required' => true,
            'stars' => 99999,
        ]))->assertRedirect('/languages');

        $user = User::firstOrFail();
        $this->assertSame('user', $user->role);
        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->placement_required);
        $this->assertSame(0, (int) $user->getAttribute('stars'));
    }

    public function test_existing_password_and_unique_email_validation_is_preserved(): void
    {
        User::factory()->create(['email' => 'learner@example.test']);
        $this->post(route('register.store'), $this->registration([
            'learner_proficiency' => 'advanced', 'password_confirmation' => 'different',
        ]))->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
    }

    public function test_migration_preserves_existing_users_and_roles(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $password = $user->password;
        $migration = require database_path('migrations/2026_10_11_100000_add_learner_proficiency_to_users_table.php');
        $migration->down();
        $migration->up();

        $user->refresh();
        $this->assertSame('admin', $user->role);
        $this->assertSame($password, $user->password);
        $this->assertSame(LearnerProficiency::Beginner, $user->learner_proficiency);
        $this->assertFalse($user->placement_required);
    }

    private function registration(array $overrides = []): array
    {
        return array_replace([
            'name' => 'Learner',
            'email' => 'learner@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $overrides);
    }
}
