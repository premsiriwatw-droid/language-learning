<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TeacherApplicationReviewController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);
        $status = $request->query('status', TeacherApplication::PENDING);
        abort_unless(in_array($status, ['all', 'pending', 'approved', 'rejected', 'needs_more_info'], true), 422);
        $applications = TeacherApplication::with('user')->withCount('documents')
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->orderByDesc('submitted_at')->orderByDesc('id')->paginate(20)->withQueryString();
        return view('admin.teacher-applications.index', compact('applications', 'status'));
    }

    public function show(Request $request, TeacherApplication $application)
    {
        abort_unless($request->user()->isAdmin(), 403);
        $application->load(['user', 'documents', 'reviews.reviewer']);
        return view('admin.teacher-applications.show', compact('application'));
    }

    public function review(Request $request, TeacherApplication $application)
    {
        abort_unless($request->user()->isAdmin(), 403);
        $data = $request->validate([
            'decision' => ['required', Rule::in(['approved', 'rejected', 'needs_more_info'])],
            'note' => ['required_unless:decision,approved', 'nullable', 'string', 'max:3000'],
            'submitted_at' => ['required', 'string'],
            'review_version' => ['required', 'integer', 'min:0'],
        ]);
        DB::transaction(function () use ($request, $application, $data) {
            // Same lock order as applicant submission: account, then application.
            $user = User::whereKey($application->user_id)->lockForUpdate()->firstOrFail();
            $current = TeacherApplication::whereKey($application->id)->lockForUpdate()->firstOrFail();
            if ($current->reviews()->count() !== (int) $data['review_version']
                || $current->status !== TeacherApplication::PENDING
                || $current->submitted_at->toISOString() !== $data['submitted_at']) {
                throw ValidationException::withMessages(['application' => 'ใบสมัครเปลี่ยนแปลงแล้ว กรุณาโหลดหน้าใหม่ก่อนพิจารณา']);
            }
            if ($user->role !== 'user') {
                throw ValidationException::withMessages(['application' => 'บัญชีนี้ไม่ได้อยู่ในสถานะผู้สมัคร กรุณาตรวจสอบบทบาทก่อน']);
            }
            if ($data['decision'] === TeacherApplication::APPROVED) {
                $documents = $current->documents()->get();
                if ($documents->isEmpty() || $documents->contains(fn ($document) => !Storage::disk('teacher_documents')->exists($document->file_path))) {
                    throw ValidationException::withMessages(['application' => 'ไม่พบเอกสารประกอบครบถ้วน กรุณาขอข้อมูลเพิ่มเติม']);
                }
            }
            $current->forceFill([
                'status' => $data['decision'], 'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(), 'review_note' => $data['note'] ?? null,
                'approved_at' => $data['decision'] === TeacherApplication::APPROVED ? now() : null,
            ])->save();
            $user->forceFill(['role' => $data['decision'] === TeacherApplication::APPROVED ? 'teacher' : 'user'])->save();
            $current->reviews()->create([
                'reviewer_id' => $request->user()->id, 'from_status' => TeacherApplication::PENDING,
                'to_status' => $data['decision'], 'note' => $data['note'] ?? null,
            ]);
        });
        return redirect()->route('admin.teacher-applications.show', $application)->with('status', 'บันทึกผลพิจารณาแล้ว');
    }
}
