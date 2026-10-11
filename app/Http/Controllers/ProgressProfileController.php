<?php

namespace App\Http\Controllers;

use App\Services\Progress\DailyQuestService;
use App\Services\Progress\LearningProgress;
use App\Services\Progress\PlayerLevelCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProgressProfileController extends Controller
{
    public function show(Request $request, LearningProgress $progress): View
    {
        $user = $request->user()->refresh();
        $summary = $progress->summary($user);

        /*
        |--------------------------------------------------------------------------
        | Displayed Streak
        |--------------------------------------------------------------------------
        |
        | current_streak ในฐานข้อมูลจะอัปเดตเมื่อผู้ใช้ทำกิจกรรม
        | แต่ตอนเปิด Profile ถ้าขาดเรียนเกิน 1 วัน
        | ต้องแสดง streak ปัจจุบันเป็น 0 โดยไม่แก้ข้อมูลในฐานข้อมูล
        |
        | longest_streak ยังคงใช้ค่าจริงในฐานข้อมูล
        |
        */

        $today = Carbon::today(config('app.timezone'));

        $lastActiveDate = $user->getRawOriginal('last_active_date')
            ? Carbon::parse(
                $user->getRawOriginal('last_active_date'),
                config('app.timezone')
            )->startOfDay()
            : null;

        $displayedCurrentStreak = $lastActiveDate
            && $lastActiveDate->greaterThanOrEqualTo(
                $today->copy()->subDay()
            )
            && $lastActiveDate->lessThanOrEqualTo($today)
                ? (int) $user->current_streak
                : 0;

        return view('frontend.profile', [
            'user' => $user,

            /*
            |--------------------------------------------------------------------------
            | Teacher Verification - PR #47
            |--------------------------------------------------------------------------
            */

            'teacherApplication' => in_array(
                $user->role,
                ['user', 'teacher'],
                true
            )
                ? $user->teacherApplication()->first()
                : null,

            'isApprovedTeacher' => $user->isApprovedTeacher(),

            /*
            |--------------------------------------------------------------------------
            | Learner Progression - PR #48
            |--------------------------------------------------------------------------
            */

            'progress' => $summary,

            'playerLevel' => app(
                PlayerLevelCalculator::class
            )->calculate($summary['xp']),

            'dailyQuests' => $user->isLearner()
                ? app(DailyQuestService::class)->today($user)
                : collect(),

            'displayedCurrentStreak' => $displayedCurrentStreak,

            /*
            |--------------------------------------------------------------------------
            | Existing Profile Data
            |--------------------------------------------------------------------------
            */

            'hasPhoto' => Storage::disk('local')->exists(
                $this->photoPath($request)
            ),

            // Auth owns this attribute.
            // No guessed HSK level or changes to User schema.
            'hskLevel' => $user->getAttribute('hsk_level'),
        ]);
    }

    public function upload(Request $request): RedirectResponse
    {
        $request->validate([
            'photo' => [
                'required',
                File::image()->max('2mb'),
                'mimes:jpg,jpeg,png,webp',
                'dimensions:max_width=4096,max_height=4096',
            ],
        ], [
            'photo.required' => 'กรุณาเลือกรูปโปรไฟล์ก่อนบันทึก',
            'photo.image' => 'กรุณาเลือกไฟล์รูปภาพที่ถูกต้อง',
            'photo.mimes' => 'รองรับเฉพาะไฟล์ JPG, PNG และ WebP',
            'photo.max' => 'รูปภาพต้องมีขนาดไม่เกิน 2 MB',
            'photo.uploaded' => 'อัปโหลดรูปไม่สำเร็จ กรุณาลองอีกครั้ง',
            'photo.dimensions' => 'รูปภาพต้องมีความกว้างและความสูงไม่เกิน 4096 พิกเซล',
        ]);

        $path = Storage::disk('local')->putFileAs(
            'profile-photos/'.$request->user()->getAuthIdentifier(),
            $request->file('photo'),
            'avatar',
        );

        if ($path === false) {
            return back()->withErrors([
                'photo' => 'บันทึกรูปไม่สำเร็จ กรุณาลองอีกครั้ง',
            ]);
        }

        return redirect()
            ->route('profile')
            ->with('status', 'อัปเดตรูปโปรไฟล์แล้ว');
    }

    public function photo(Request $request): StreamedResponse
    {
        $disk = Storage::disk('local');
        $path = $this->photoPath($request);

        abort_unless($disk->exists($path), 404);

        return $disk->response($path, null, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function photoPath(Request $request): string
    {
        return 'profile-photos/'
            .$request->user()->getAuthIdentifier()
            .'/avatar';
    }
}