<?php

namespace Database\Seeders;

use App\Enums\NotificationTopic;
use App\Events\Notifications\UserNotificationRequested;
use App\Models\TeacherApplication;
use App\Models\User;
use App\Services\Progress\StarRewardService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/** Explicit local demo fixtures only; never part of DatabaseSeeder. */
class IntegrationDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Demo users may only be seeded in local/testing environments.');
        }
        $password = config('demo.password');
        if (! is_string($password) || strlen($password) < 12) {
            throw new RuntimeException('Set DEMO_PASSWORD to at least 12 characters before seeding demo users.');
        }
        $this->call(ShopItemSeeder::class);
        DB::transaction(function () use ($password): void {
            foreach (['learner' => 'user', 'pending-teacher' => 'user', 'verified-teacher' => 'teacher', 'admin' => 'admin'] as $name => $role) {
                $user = User::firstOrCreate(['email' => $name.'@demo.langlearn.test'], [
                    'name' => 'Demo '.$name, 'password' => $password, 'role' => $role,
                ]);
                if (! $user->wasRecentlyCreated) {
                    continue;
                }
                if (in_array($name, ['pending-teacher', 'verified-teacher'], true)) {
                    $approved = $name === 'verified-teacher';
                    $application = TeacherApplication::forceCreate([
                        'user_id' => $user->id, 'full_name' => $user->name, 'bio' => 'Local demo fixture',
                        'teaching_experience' => 'Demo only', 'teaching_languages' => ['English'],
                        'status' => $approved ? TeacherApplication::APPROVED : TeacherApplication::PENDING,
                        'submitted_at' => now(), 'approved_at' => $approved ? now() : null,
                    ]);
                    $path = 'demo/'.$application->id.'/sample.png';
                    $image = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jX1sAAAAASUVORK5CYII=');
                    if (! Storage::disk('teacher_documents')->put($path, $image)) {
                        throw new RuntimeException('Cannot write private demo document.');
                    }
                    $application->documents()->create([
                        'document_type' => 'certificate', 'file_path' => $path, 'original_name' => 'DEMO-ONLY.png',
                        'mime_type' => 'image/png', 'file_size' => strlen($image), 'uploaded_at' => now(),
                    ]);
                }
                if ($name === 'learner') {
                    app(StarRewardService::class)->add($user, 'demo:starter', 100);
                    UserNotificationRequested::dispatch($user->id, NotificationTopic::RecommendationNew,
                        'demo:welcome', 'ยินดีต้อนรับสู่เดโม', 'ลองใช้ดาวที่ร้านค้า แล้วเลือกไอเทมแต่งโปรไฟล์', '/shop');
                }
            }
        });
    }
}
