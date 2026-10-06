<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use App\Models\Vocabulary;
use App\Services\Progress\LearningProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesContentLesson;
use Tests\TestCase;

class ProgressProfileTest extends TestCase
{
    use CreatesContentLesson;
    use RefreshDatabase;

    private function photo(): UploadedFile
    {
        // PNG ขนาดเล็ก ไม่ต้องใช้ GD extension
        return UploadedFile::fake()->createWithContent(
            'avatar.png',
            base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jC1sAAAAASUVORK5CYII='
            )
        );
    }

    public function test_profile_and_photo_endpoints_require_login(): void
    {
        $this->get('/profile')->assertRedirect('/login');
        $this->get('/profile/photo')->assertRedirect('/login');

        $this->post('/profile/upload', [
            'photo' => $this->photo(),
        ])->assertRedirect('/login');
    }

    public function test_new_profile_has_real_empty_state_and_single_layout(): void
    {
        Storage::fake('local');

        $user = User::factory()->create([
            'name' => 'ผู้เรียนทดสอบ',
        ]);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk()
            ->assertSee('ผู้เรียนทดสอบ')
            ->assertSee('ยังไม่ระบุ')
            ->assertSee('0 / 0 บทเรียน')
            ->assertSee('บทเรียนกำลังมา')
            ->assertDontSee('45%')
            ->assertDontSee('HSK 1')
            ->assertSee('href="' . url('/languages') . '"', false);

        $this->assertSame(
            1,
            substr_count($response->getContent(), '<!DOCTYPE html>')
        );

        $this->assertSame(
            1,
            substr_count($response->getContent(), 'id="main-content"')
        );
    }

    public function test_logout_form_uses_existing_route_and_ends_the_session(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        foreach (['/profile', '/languages'] as $page) {
            $this->actingAs($user)->get($page)->assertOk()
                ->assertSee('action="'.route('logout').'" method="POST"', false)
                ->assertSee('name="_token"', false)
                ->assertSee('ออกจากระบบ');
        }

        $this->withSession(['profile_test_state' => 'signed in'])->post(route('logout'))
            ->assertRedirect(route('login'))->assertSessionMissing('profile_test_state');
        $this->assertGuest();
        $this->get('/profile')->assertRedirect(route('login'));
        $this->get('/profile/photo')->assertRedirect(route('login'));
        $this->get('/languages')->assertDontSee('ออกจากระบบ');
    }

    public function test_upload_is_private_replaces_previous_photo_and_does_not_change_user_schema(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/profile/photo')
            ->assertNotFound();

        $this->post('/profile/upload', [
            'photo' => $this->photo(),
        ])
            ->assertRedirect('/profile')
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status');

        $path = 'profile-photos/' . $user->id . '/avatar';

        Storage::disk('local')->assertExists($path);

        $this->get('/profile/photo')
            ->assertOk()
            ->assertHeader('content-type', 'image/png')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->get('/profile')->assertSee('id="avatar-preview" src="'.route('profile.photo').'"', false);
        $this->get('/languages')->assertSee('<img src="'.route('profile.photo').'"', false);
        $this->post('/profile/upload', ['photo' => $this->photo()])
            ->assertSessionHasNoErrors();
        $this->assertCount(1, Storage::disk('local')->allFiles('profile-photos/'.$user->id));
        $this->assertArrayNotHasKey('profile_photo_path', $user->fresh()->getAttributes());

        $other = User::factory()->create();
        $this->actingAs($other)->get('/profile/photo')->assertNotFound();
        $this->get('/profile')->assertDontSee('src="'.route('profile.photo').'"', false);
        $this->get('/languages')->assertDontSee('src="'.route('profile.photo').'"', false);
    }

    public function test_invalid_uploads_preserve_existing_photo(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        $this->actingAs($user)->post('/profile/upload', [
            'photo' => $this->photo(),
        ])->assertSessionHasNoErrors();

        $path = 'profile-photos/' . $user->id . '/avatar';
        $original = Storage::disk('local')->get($path);

        $invalid = [
            UploadedFile::fake()->createWithContent(
                'bad.jpg',
                '<?php echo "bad";'
            ),
            UploadedFile::fake()->createWithContent(
                'bad.svg',
                '<svg xmlns="http://www.w3.org/2000/svg"></svg>'
            ),
            $this->photo()->size(2049),
        ];

        foreach ($invalid as $file) {
            $this->post('/profile/upload', [
                'photo' => $file,
            ])->assertSessionHasErrors('photo');

            $this->assertSame(
                $original,
                Storage::disk('local')->get($path)
            );
        }

        $this->post('/profile/upload', [])
            ->assertSessionHasErrors('photo');
    }

    public function test_visiting_a_valid_step_remembers_it_but_never_awards_completion(): void
    {
        $user = User::factory()->create();
        $lesson = $this->createContentLesson();

        Vocabulary::factory()->count(3)->create([
            'lesson_id' => $lesson->id,
        ]);

        $this->actingAs($user);

        $firstStepUrl = route('lessons.learn.step', [
            'lesson' => $lesson->id,
            'step' => 1,
        ]);

        $secondStepUrl = route('lessons.learn.step', [
            'lesson' => $lesson->id,
            'step' => 2,
        ]);

        // ข้ามไปขั้น 2 ไม่ได้ และ redirect ไม่บันทึกการเข้าชม
        $this->get($secondStepUrl)
            ->assertRedirect($firstStepUrl);

        $this->assertDatabaseCount('lesson_progress', 0);

        // เปิดขั้น 1 แล้วกดยืนยันว่าเรียนคำศัพท์แล้ว
        $this->get($firstStepUrl)->assertOk();

        $this->post(route('lessons.learn.submit', [
            'lesson' => $lesson->id,
            'step' => 1,
        ]))->assertRedirect($secondStepUrl);

        // ขั้น 2 เปิดได้หลังผ่านขั้น 1
        $this->get($secondStepUrl)->assertOk();

        $this->assertDatabaseHas('lesson_progress', [
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'last_step' => 2,
            'completed_at' => null,
            'xp' => 0,
            'stars' => 0,
        ]);

        $this->get('/profile')
            ->assertOk()
            ->assertSee($lesson->title)
            ->assertSee('ขั้นที่ 2')
            ->assertSee('0 / 1 บทเรียน')
            ->assertSee('href="' . $secondStepUrl . '"', false);

        // เปิดขั้นข้างหน้าต้องกลับขั้นปัจจุบัน
        $this->get(route('lessons.learn.step', [
            'lesson' => $lesson->id,
            'step' => 3,
        ]))->assertRedirect($secondStepUrl);

        // ย้อนดูขั้นที่ผ่านมาได้
        $this->get($firstStepUrl)->assertOk();

        // กลับขั้น 2 เพื่อให้เป็นขั้นที่เปิดล่าสุด
        $this->get($secondStepUrl)->assertOk();

        // เปิด URL ท้ายบทไม่ทำให้เรียนจบหรือได้รางวัล
        $this->get(route('lessons.learn.step', [
            'lesson' => $lesson->id,
            'step' => 999,
        ]))->assertRedirect($secondStepUrl);

        $this->assertDatabaseHas('lesson_progress', [
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'last_step' => 2,
            'completed_at' => null,
            'xp' => 0,
            'stars' => 0,
        ]);

        $this->get('/lessons/999999/learn/1')
            ->assertNotFound();

        $this->assertDatabaseCount('lesson_progress', 1);
    }

    public function test_guest_learning_does_not_create_user_progress(): void
    {
        $lesson = $this->createContentLesson();

        Vocabulary::factory()->create([
            'lesson_id' => $lesson->id,
        ]);

        $this->get('/lessons/' . $lesson->id . '/learn/1')
            ->assertOk();

        $this->assertDatabaseCount('lesson_progress', 0);
    }

    public function test_backend_completion_is_idempotent_and_summary_is_user_scoped(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $lesson = $this->createContentLesson();

        $second = Lesson::forceCreate([
            'unit_id' => $lesson->unit_id,
            'title' => 'บทเรียนที่สอง',
        ]);

        $progress = app(LearningProgress::class);

        $progress->visit($user, $second, 3);
        $progress->complete($user, $lesson, 20, 2);
        $progress->complete($user, $lesson, 999, 99);
        $progress->complete($other, $second, 500, 50);

        $summary = $progress->summary($user);

        $this->assertSame(1, $summary['completed']);
        $this->assertSame(50, $summary['percentage']);
        $this->assertSame(20, $summary['xp']);
        $this->assertSame(2, $summary['stars']);
        $this->assertSame(
            $second->id,
            $summary['latest']->lesson_id
        );

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertSee('50%')
            ->assertSee('1 / 2 บทเรียน')
            ->assertSee('บทเรียนที่สอง')
            ->assertDontSee('500');

        $progress->visit($user, $lesson, 1);

        $savedProgress = LessonProgress::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->firstOrFail();

        $this->assertNotNull($savedProgress->completed_at);
        $this->assertSame(20, $progress->summary($user)['xp']);
    }

    public function test_negative_rewards_are_rejected_without_creating_progress(): void
    {
        $user = User::factory()->create();
        $lesson = $this->createContentLesson();

        try {
            app(LearningProgress::class)->complete(
                $user,
                $lesson,
                -1,
                1
            );

            $this->fail('Negative rewards should be rejected.');
        } catch (\InvalidArgumentException) {
            $this->assertDatabaseCount('lesson_progress', 0);
        }
    }

    public function test_profile_handles_missing_progress_migration_and_escapes_user_content(): void
    {
        Storage::fake('local');
        Schema::drop('lesson_progress');

        $user = User::factory()->create([
            'name' => '<script>alert(1)</script>',
        ]);

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertSee('ขณะนี้ยังบันทึกความคืบหน้าไม่ได้')
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);

        $lesson = $this->createContentLesson();

        Vocabulary::factory()->create([
            'lesson_id' => $lesson->id,
        ]);

        $this->get('/lessons/' . $lesson->id . '/learn/1')
            ->assertOk();
    }
}