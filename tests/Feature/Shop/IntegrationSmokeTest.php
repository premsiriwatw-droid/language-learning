<?php

namespace Tests\Feature\Shop;

use App\Models\User;
use Database\Seeders\IntegrationDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class IntegrationSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_roles_and_cross_module_pages_work_and_reseed_preserves_accounts(): void
    {
        Storage::fake('teacher_documents');
        config()->set('demo.password', 'Only-for-this-test-123');
        $this->seed(IntegrationDemoSeeder::class);
        $learner = User::where('email', 'learner@demo.langlearn.test')->sole();
        $learner->update(['name' => 'Preserve my name']);
        $this->seed(IntegrationDemoSeeder::class);
        $this->assertDatabaseCount('users', 4);
        $this->assertDatabaseCount('shop_items', 10);
        $this->assertDatabaseCount('star_rewards', 1);
        $this->assertDatabaseCount('teacher_documents', 2);
        $this->assertSame('Preserve my name', $learner->fresh()->name);
        foreach (User::all() as $user) {
            $this->actingAs($user);
            foreach (['profile', 'shop.index', 'inventory.index', 'notifications.index', 'languages.index', 'teacher-courses.index'] as $route) {
                $this->get(route($route))->assertOk();
            }
            if ($user->isApprovedTeacher()) {
                $this->get(route('teacher.courses.index'))->assertOk();
            } else {
                $this->get(route('teacher.courses.index'))->assertForbidden();
            }
            if ($user->isAdmin()) {
                $this->get(route('admin.dashboard'))->assertOk();
            }
        }
    }

    public function test_demo_requires_explicit_password_and_cannot_run_in_production(): void
    {
        config()->set('demo.password', null);
        try {
            $this->seed(IntegrationDemoSeeder::class);
            $this->fail('Missing demo password should be rejected');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('DEMO_PASSWORD', $exception->getMessage());
        }
        $this->app->instance('env', 'production');
        config()->set('demo.password', 'Only-for-this-test-123');
        try {
            app(IntegrationDemoSeeder::class)->run();
            $this->fail('Production demo seeding should be rejected');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('local/testing', $exception->getMessage());
        } finally {
            $this->app->instance('env', 'testing');
        }
        $this->assertDatabaseCount('users', 0);
    }

    public function test_routes_keep_existing_module_contracts_without_name_or_method_collisions(): void
    {
        $names = [];
        $endpoints = [];
        foreach (Route::getRoutes() as $route) {
            if ($route->getName() !== null) {
                $this->assertArrayNotHasKey($route->getName(), $names);
                $names[$route->getName()] = true;
            }
            foreach ($route->methods() as $method) {
                $key = $method.' '.$route->getDomain().' '.$route->uri();
                $this->assertArrayNotHasKey($key, $endpoints);
                $endpoints[$key] = true;
            }
        }
        foreach (['placement.show', 'daily-quests.claim', 'teacher.application.show', 'teacher.courses.dashboard',
            'teacher-courses.enroll', 'shop.purchase', 'notifications.read-all'] as $name) {
            $this->assertArrayHasKey($name, $names);
        }
    }
}
