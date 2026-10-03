<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\User;
use Database\Seeders\LearningStructureSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_regular_user_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/dashboard');

        $response->assertForbidden();
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this
            ->actingAs($admin)
            ->get('/admin/dashboard');

        $response->assertOk();
    }

    public function test_regular_user_cannot_access_content_management(): void
    {
        $this->seed(LearningStructureSeeder::class);

        $lesson = Lesson::query()->firstOrFail();

        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('content.index', $lesson));

        $response->assertForbidden();
    }

    public function test_admin_can_access_content_management(): void
    {
        $this->seed(LearningStructureSeeder::class);

        $lesson = Lesson::query()->firstOrFail();

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this
            ->actingAs($admin)
            ->get(route('content.index', $lesson));

        $response->assertOk();
    }
}