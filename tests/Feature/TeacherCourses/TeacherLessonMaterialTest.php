<?php

namespace Tests\Feature\TeacherCourses;

use App\Models\CourseEnrollment;
use App\Models\TeacherCourse;
use App\Models\TeacherLesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesTeacherCourses;
use Tests\TestCase;

class TeacherLessonMaterialTest extends TestCase
{
    use CreatesTeacherCourses;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(TeacherLesson::DISK);
    }

    private function lesson(TeacherCourse $course, array $attributes = []): TeacherLesson
    {
        $lesson = new TeacherLesson();
        $lesson->forceFill($attributes + [
            'teacher_course_id' => $course->id,
            'title' => 'Teacher lesson '.uniqid(),
            'body' => 'Read this first.',
            'position' => (int) $course->teacherLessons()->max('position') + 1,
        ])->save();

        return $lesson;
    }

    private function lessonWithFiles(TeacherCourse $course): TeacherLesson
    {
        Storage::disk(TeacherLesson::DISK)->put('teacher-course-materials/'.$course->id.'/doc.pdf', '%PDF-1.4 demo');
        Storage::disk(TeacherLesson::DISK)->put('teacher-course-materials/'.$course->id.'/clip.mp4', 'fake-video-bytes');

        return $this->lesson($course, [
            'title' => 'Greetings',
            'document_path' => 'teacher-course-materials/'.$course->id.'/doc.pdf',
            'document_name' => 'greetings-sheet.pdf', 'document_size' => 13,
            'video_path' => 'teacher-course-materials/'.$course->id.'/clip.mp4',
            'video_name' => 'greetings.mp4', 'video_size' => 16,
        ]);
    }

    public function test_teacher_adds_lesson_with_text_document_and_uploaded_video(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher, null, [], fn ($f) => $f->draft());

        $this->actingAs($teacher)
            ->post(route('teacher.courses.teacher-lessons.store', $course), [
                'title' => 'Ordering food',
                'body' => "Useful phrases:\nI would like…",
                'document' => UploadedFile::fake()->create('menu-phrases.pdf', 120, 'application/pdf'),
                'video_file' => UploadedFile::fake()->create('ordering.mp4', 900, 'video/mp4'),
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('teacher.courses.edit', $course).'#my-lessons');

        $lesson = TeacherLesson::sole();
        $this->assertSame($course->id, $lesson->teacher_course_id);
        $this->assertSame(1, $lesson->position);
        $this->assertSame('menu-phrases.pdf', $lesson->document_name);
        $this->assertSame('ordering.mp4', $lesson->video_name);
        $this->assertStringStartsWith('teacher-course-materials/'.$course->id.'/', $lesson->document_path);
        Storage::disk(TeacherLesson::DISK)->assertExists([$lesson->document_path, $lesson->video_path]);
        $this->assertStringEndsWith('.mp4', $lesson->video_path);

        $this->lesson($course, ['title' => 'Second lesson']);
        $this->actingAs($teacher)->get(route('teacher.courses.edit', $course))
            ->assertOk()->assertSeeInOrder(['บทเรียนของฉัน', 'Ordering food', 'menu-phrases.pdf', 'Second lesson']);
    }

    public function test_lesson_validation(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher, null, [], fn ($f) => $f->draft());
        $store = route('teacher.courses.teacher-lessons.store', $course);

        $this->actingAs($teacher)->post($store, ['title' => 'Empty'])->assertSessionHasErrors('body');
        $this->actingAs($teacher)->post($store, ['title' => 'Bad link', 'video_url' => 'https://evil.example/watch?v=dQw4w9WgXcQ'])
            ->assertSessionHasErrors('video_url');
        $this->actingAs($teacher)->post($store, ['title' => 'Script', 'video_url' => 'javascript:alert(1)'])
            ->assertSessionHasErrors('video_url');
        $this->actingAs($teacher)->post($store, [
            'title' => 'Both', 'video_url' => 'https://youtu.be/dQw4w9WgXcQ',
            'video_file' => UploadedFile::fake()->create('a.mp4', 10, 'video/mp4'),
        ])->assertSessionHasErrors('video_file');
        $this->actingAs($teacher)->post($store, [
            'title' => 'Page', 'document' => UploadedFile::fake()->create('page.html', 1, 'text/html'),
        ])->assertSessionHasErrors('document');
        $this->actingAs($teacher)->post($store, [
            'title' => 'Program', 'document' => UploadedFile::fake()->create('setup.exe', 1, 'application/x-msdownload'),
        ])->assertSessionHasErrors('document');
        $this->actingAs($teacher)->post($store, [
            'title' => 'Huge', 'document' => UploadedFile::fake()->create('big.pdf', TeacherLesson::DOCUMENT_MAX_KB + 1, 'application/pdf'),
        ])->assertSessionHasErrors('document');
        $this->actingAs($teacher)->post($store, [
            'title' => 'Wrong video', 'video_file' => UploadedFile::fake()->create('clip.avi', 10, 'video/x-msvideo'),
        ])->assertSessionHasErrors('video_file');

        $this->assertSame(0, TeacherLesson::count());
        $this->assertSame([], Storage::disk(TeacherLesson::DISK)->allFiles());

        $this->actingAs($teacher)->post($store, ['title' => 'Video only', 'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=10'])
            ->assertSessionHasNoErrors();
        $this->assertSame('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', TeacherLesson::sole()->videoEmbedUrl());
    }

    public function test_only_youtube_and_vimeo_links_are_embedded(): void
    {
        $this->assertSame('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', TeacherLesson::embedUrlFor('https://youtu.be/dQw4w9WgXcQ'));
        $this->assertSame('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', TeacherLesson::embedUrlFor('https://m.youtube.com/shorts/dQw4w9WgXcQ'));
        $this->assertSame('https://player.vimeo.com/video/76979871', TeacherLesson::embedUrlFor('https://vimeo.com/76979871'));
        $this->assertNull(TeacherLesson::embedUrlFor('https://youtube.com.evil.example/watch?v=dQw4w9WgXcQ'));
        $this->assertNull(TeacherLesson::embedUrlFor('https://www.youtube.com/watch?v="><script>'));
        $this->assertNull(TeacherLesson::embedUrlFor('javascript://youtube.com/%0Aalert(1)'));
        $this->assertNull(TeacherLesson::embedUrlFor('https://example.com/video.mp4'));
        $this->assertNull(TeacherLesson::embedUrlFor('https://www.youtube.com/watch?v[]=dQw4w9WgXcQ'));
        $this->assertNull(TeacherLesson::embedUrlFor('https://www.youtube.com/watch?v[a]=dQw4w9WgXcQ&v[b]=x'));
    }

    public function test_only_learning_members_owner_and_admin_open_materials(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher, null, [], fn ($f) => $f->approvalRequired());
        $lesson = $this->lessonWithFiles($course);
        $routes = [
            route('teacher-courses.lessons.show', [$course, $lesson]),
            route('teacher-courses.lessons.document', [$course, $lesson]),
            route('teacher-courses.lessons.video', [$course, $lesson]),
        ];

        $stranger = $this->learner();
        $waiting = $this->learner();
        $this->actingAs($waiting)->post(route('teacher-courses.enroll', $course));
        $this->assertSame(CourseEnrollment::PENDING, CourseEnrollment::sole()->status);

        foreach ([$stranger, $waiting, $this->approvedTeacher()] as $user) {
            foreach ($routes as $url) {
                $this->actingAs($user)->get($url)->assertForbidden();
            }
        }

        // Non-members see that lessons exist, but no link to the content.
        $this->actingAs($stranger)->get(route('teacher-courses.show', $course))
            ->assertOk()->assertSee('Greetings')->assertDontSee($routes[0]);

        $enrollment = CourseEnrollment::sole();
        $this->actingAs($teacher)->patch(route('teacher.courses.enrollments.update', $enrollment), ['action' => 'approve']);
        $this->assertSame(CourseEnrollment::ACTIVE, $enrollment->fresh()->status);

        $admin = User::factory()->create(['role' => 'admin']);
        foreach ([$waiting, $teacher, $admin] as $user) {
            $this->actingAs($user)->get($routes[0])->assertOk()->assertSee('Greetings')->assertSee('greetings-sheet.pdf');
            $download = $this->actingAs($user)->get($routes[1])->assertOk();
            $this->assertStringContainsString('attachment', $download->headers->get('Content-Disposition'));
            $this->assertStringContainsString('greetings-sheet.pdf', $download->headers->get('Content-Disposition'));
            $this->assertSame('nosniff', $download->headers->get('X-Content-Type-Options'));
            $video = $this->actingAs($user)->get($routes[2])->assertOk();
            $this->assertSame('video/mp4', $video->headers->get('Content-Type'));
        }

        $this->actingAs($waiting)->get(route('teacher-courses.show', $course))->assertSee($routes[0]);
        // Files are never reachable through a public storage URL.
        $this->assertContains($this->get('/storage/'.$lesson->document_path)->status(), [403, 404]);
    }

    public function test_lesson_must_belong_to_the_course_in_the_url(): void
    {
        $teacher = $this->approvedTeacher();
        $intruder = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher);
        $ownCourse = $this->teacherCourse($intruder);
        $lesson = $this->lessonWithFiles($course);

        // Owner of another course cannot reach this lesson through their own course id.
        $this->actingAs($intruder)->get(route('teacher.courses.teacher-lessons.edit', [$ownCourse, $lesson]))->assertNotFound();
        $this->actingAs($intruder)->delete(route('teacher.courses.teacher-lessons.destroy', [$ownCourse, $lesson]))->assertNotFound();
        $this->actingAs($intruder)->get(route('teacher-courses.lessons.document', [$ownCourse, $lesson]))->assertNotFound();

        // …or directly.
        $this->actingAs($intruder)->get(route('teacher.courses.teacher-lessons.edit', [$course, $lesson]))->assertForbidden();
        $this->actingAs($intruder)->put(route('teacher.courses.teacher-lessons.update', [$course, $lesson]), ['title' => 'Hacked', 'body' => 'x'])->assertForbidden();
        $this->actingAs($intruder)->delete(route('teacher.courses.teacher-lessons.destroy', [$course, $lesson]))->assertForbidden();
        $this->actingAs($intruder)->post(route('teacher.courses.teacher-lessons.store', $course), ['title' => 'Spam', 'body' => 'x'])->assertForbidden();
        $this->actingAs($this->pendingTeacher())->get(route('teacher.courses.teacher-lessons.create', $course))->assertForbidden();

        $this->assertSame('Greetings', $lesson->fresh()->title);
        $this->assertSame(1, TeacherLesson::count());
    }

    public function test_replacing_and_removing_files_cleans_up_storage(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher, null, [], fn ($f) => $f->draft());
        $lesson = $this->lessonWithFiles($course);
        [$oldDocument, $oldVideo] = [$lesson->document_path, $lesson->video_path];

        $this->actingAs($teacher)->put(route('teacher.courses.teacher-lessons.update', [$course, $lesson]), [
            'title' => 'Greetings v2',
            'body' => 'Updated.',
            'document' => UploadedFile::fake()->create('new-sheet.docx', 50, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
            'video_url' => 'https://vimeo.com/76979871',
        ])->assertSessionHasNoErrors();

        $lesson->refresh();
        $this->assertSame('new-sheet.docx', $lesson->document_name);
        $this->assertNull($lesson->video_path);
        $this->assertSame('https://player.vimeo.com/video/76979871', $lesson->videoEmbedUrl());
        Storage::disk(TeacherLesson::DISK)->assertMissing([$oldDocument, $oldVideo]);
        Storage::disk(TeacherLesson::DISK)->assertExists($lesson->document_path);

        $this->actingAs($teacher)->put(route('teacher.courses.teacher-lessons.update', [$course, $lesson]), [
            'title' => 'Greetings v2', 'body' => 'Updated.', 'remove_document' => '1', 'remove_video' => '1',
        ])->assertSessionHasNoErrors();
        $lesson->refresh();
        $this->assertFalse($lesson->hasDocument());
        $this->assertFalse($lesson->hasVideo());
        $this->assertSame([], Storage::disk(TeacherLesson::DISK)->allFiles());

        // Removing everything is not allowed.
        $this->actingAs($teacher)->put(route('teacher.courses.teacher-lessons.update', [$course, $lesson]), ['title' => 'Empty', 'body' => ''])
            ->assertSessionHasErrors('body');

        $withFiles = $this->lessonWithFiles($course);
        $this->actingAs($teacher)->delete(route('teacher.courses.teacher-lessons.destroy', [$course, $withFiles]))->assertSessionHasNoErrors();
        $this->assertModelMissing($withFiles);
        $this->assertSame([], Storage::disk(TeacherLesson::DISK)->allFiles());
    }

    public function test_teacher_reorders_lessons(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher, null, [], fn ($f) => $f->draft());
        $first = $this->lesson($course, ['title' => 'First']);
        $second = $this->lesson($course, ['title' => 'Second']);
        $third = $this->lesson($course, ['title' => 'Third']);

        $this->actingAs($teacher)->patch(route('teacher.courses.teacher-lessons.move', [$course, $third]), ['direction' => 'up']);
        $this->actingAs($teacher)->patch(route('teacher.courses.teacher-lessons.move', [$course, $first]), ['direction' => 'up']);

        $this->assertSame(['First', 'Third', 'Second'], $course->teacherLessons()->pluck('title')->all());

        $this->actingAs($teacher)->delete(route('teacher.courses.teacher-lessons.destroy', [$course, $first]));
        $this->assertSame([1, 2], $course->teacherLessons()->pluck('position')->all());
        $this->assertSame(['Third', 'Second'], $course->teacherLessons()->pluck('title')->all());
        $this->assertModelExists($second);
    }

    public function test_live_course_keeps_at_least_one_lesson(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher);
        $course->lessons()->detach();
        $only = $this->lesson($course, ['title' => 'Only lesson']);

        $this->actingAs($teacher)->delete(route('teacher.courses.teacher-lessons.destroy', [$course, $only]))
            ->assertSessionHasErrors('lesson');
        $this->assertModelExists($only);
        $this->assertSame(TeacherCourse::PUBLISHED, $course->fresh()->status);

        // Own lessons count, so the system-lesson list may now be empty.
        $this->actingAs($teacher)->put(route('teacher.courses.update', $course), [
            'title' => $course->title, 'description' => $course->description, 'language_id' => $course->language_id,
            'level' => $course->level, 'price_type' => 'free', 'price' => '', 'enrollment_mode' => 'instant', 'lessons' => [],
        ])->assertSessionHasNoErrors();
    }

    public function test_editing_a_published_course_sends_it_back_for_review_but_learners_keep_access(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher, null, ['title' => 'Live course']);
        $lesson = $this->lesson($course, ['title' => 'Week 1']);
        $learner = $this->learner();
        $this->actingAs($learner)->post(route('teacher-courses.enroll', $course));

        $this->actingAs($teacher)->put(route('teacher.courses.teacher-lessons.update', [$course, $lesson]), [
            'title' => 'Week 1 (new video)', 'body' => 'Watch this', 'video_url' => 'https://youtu.be/dQw4w9WgXcQ',
        ])->assertSessionHasNoErrors()->assertSessionHas('success', fn ($message) => str_contains($message, 'รอแอดมินตรวจ'));

        $course->refresh();
        $this->assertSame(TeacherCourse::PENDING_REVIEW, $course->status);
        $this->assertNull($course->approved_at);

        $this->actingAs($this->learner())->get(route('teacher-courses.index'))->assertDontSee('Live course');
        $this->actingAs($this->learner())->post(route('teacher-courses.enroll', $course))->assertForbidden();
        $this->actingAs($learner)->get(route('teacher-courses.lessons.show', [$course, $lesson]))
            ->assertOk()->assertSee('Week 1 (new video)')->assertSee('youtube-nocookie.com/embed/dQw4w9WgXcQ', false);
    }

    public function test_learner_page_shows_teacher_lessons_and_navigation(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher);
        $course->lessons()->detach();
        $first = $this->lesson($course, ['title' => 'Intro', 'body' => "Line one\n<b>not bold</b>"]);
        $second = $this->lesson($course, ['title' => 'Practice']);
        $learner = $this->learner();
        $this->actingAs($learner)->post(route('teacher-courses.enroll', $course));

        $this->actingAs($learner)->get(route('teacher-courses.show', $course))
            ->assertOk()->assertSee('บทเรียนจากผู้สอน')->assertSeeInOrder(['Intro', 'Practice'])
            ->assertSee('2 บท')->assertDontSee('แบบฝึกจากบทเรียนหลัก');

        $this->actingAs($learner)->get(route('teacher-courses.lessons.show', [$course, $first]))
            ->assertOk()->assertSee('บทที่ 1 จาก 2')->assertSee('&lt;b&gt;not bold&lt;/b&gt;', false)
            ->assertSee(route('teacher-courses.lessons.show', [$course, $second]));

        $this->actingAs($learner)->get(route('teacher-courses.index'))->assertSee('2 บท');
    }

    public function test_malformed_youtube_link_is_a_validation_error_not_a_crash(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher, null, [], fn ($f) => $f->draft());

        foreach (['https://www.youtube.com/watch?v[]=dQw4w9WgXcQ', 'https://youtube.com/watch?v[x]=1'] as $url) {
            $this->actingAs($teacher)
                ->post(route('teacher.courses.teacher-lessons.store', $course), ['title' => 'Video', 'video_url' => $url])
                ->assertStatus(302)
                ->assertSessionHasErrors('video_url');
        }
        $this->assertSame(0, TeacherLesson::count());

        // Even a bad value already in the database must not break the pages.
        $lesson = $this->lesson($course, ['title' => 'Legacy', 'video_url' => 'https://www.youtube.com/watch?v[]=dQw4w9WgXcQ']);
        $this->actingAs($teacher)->get(route('teacher-courses.lessons.show', [$course, $lesson]))->assertOk();
    }

    public function test_removing_the_only_video_cannot_leave_an_empty_lesson(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher, null, [], fn ($f) => $f->draft());
        $linkOnly = $this->lesson($course, ['title' => 'Link only', 'body' => null, 'video_url' => 'https://youtu.be/dQw4w9WgXcQ']);
        $fileOnly = $this->lessonWithFiles($course);
        $fileOnly->forceFill(['body' => null, 'document_path' => null, 'document_name' => null, 'document_size' => null])->save();

        // The form still sends the old URL together with "remove video".
        $this->actingAs($teacher)->put(route('teacher.courses.teacher-lessons.update', [$course, $linkOnly]), [
            'title' => 'Link only', 'body' => '', 'video_url' => 'https://youtu.be/dQw4w9WgXcQ', 'remove_video' => '1',
        ])->assertSessionHasErrors('body');
        $this->assertSame('https://youtu.be/dQw4w9WgXcQ', $linkOnly->fresh()->video_url);

        $this->actingAs($teacher)->put(route('teacher.courses.teacher-lessons.update', [$course, $fileOnly]), [
            'title' => $fileOnly->title, 'body' => '', 'remove_video' => '1',
        ])->assertSessionHasErrors('body');
        $this->assertTrue($fileOnly->fresh()->hasVideoFile());
        Storage::disk(TeacherLesson::DISK)->assertExists($fileOnly->video_path);

        // Removing the video is fine when something else remains, or when a new video replaces it.
        $this->actingAs($teacher)->put(route('teacher.courses.teacher-lessons.update', [$course, $linkOnly]), [
            'title' => 'Link only', 'body' => 'Now with text', 'video_url' => 'https://youtu.be/dQw4w9WgXcQ', 'remove_video' => '1',
        ])->assertSessionHasNoErrors();
        $this->assertNull($linkOnly->fresh()->video_url);

        $this->actingAs($teacher)->put(route('teacher.courses.teacher-lessons.update', [$course, $fileOnly]), [
            'title' => $fileOnly->title, 'body' => '', 'remove_video' => '1',
            'video_file' => UploadedFile::fake()->create('new.webm', 20, 'video/webm'),
        ])->assertSessionHasNoErrors();
        $this->assertSame('new.webm', $fileOnly->fresh()->video_name);
    }
}
