<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\TeacherApplication;
use App\Models\TeacherDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class TeacherApplicationController extends Controller
{
    private function ensureApplicant(Request $request): void
    {
        abort_unless(in_array($request->user()->role, ['user', 'teacher'], true), 403);
    }

    public function show(Request $request)
    {
        $this->ensureApplicant($request);
        $application = $request->user()->teacherApplication()->with(['documents', 'reviews'])->first();
        $languages = Language::orderBy('name')->pluck('name');
        $canEdit = $request->user()->role === 'user'
            && (!$application || in_array($application->status, [TeacherApplication::REJECTED, TeacherApplication::NEEDS_MORE_INFO], true));
        return view('teacher.application', compact('application', 'languages', 'canEdit'));
    }

    public function store(Request $request)
    {
        $this->ensureApplicant($request);
        abort_unless($request->user()->role === 'user', 403);
        // Report PHP upload errors before Laravel replaces them with a generic message.
        $uploadedDocuments = $request->files->get('documents', []);
        if (is_array($uploadedDocuments)) {
            foreach ($uploadedDocuments as $index => $file) {
                if (!$file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile || $file->isValid()) {
                    continue;
                }
                $code = $file->getError();
                $message = match ($code) {
                    UPLOAD_ERR_INI_SIZE => 'ไฟล์เกินขนาดที่ PHP อนุญาต (' . ini_get('upload_max_filesize') . ')',
                    UPLOAD_ERR_FORM_SIZE => 'ไฟล์เกินขนาดที่แบบฟอร์มอนุญาต',
                    UPLOAD_ERR_PARTIAL => 'ส่งไฟล์มาไม่ครบ กรุณาเลือกไฟล์และลองใหม่',
                    UPLOAD_ERR_NO_FILE => 'กรุณาเลือกไฟล์เอกสาร',
                    UPLOAD_ERR_NO_TMP_DIR => 'PHP ไม่พบโฟลเดอร์ชั่วคราวสำหรับอัปโหลด ต้องตั้งค่า upload_tmp_dir',
                    UPLOAD_ERR_CANT_WRITE => 'PHP เขียนไฟล์ชั่วคราวไม่ได้ ต้องตรวจสิทธิ์โฟลเดอร์ upload_tmp_dir และพื้นที่ดิสก์',
                    UPLOAD_ERR_EXTENSION => 'ส่วนขยาย PHP หยุดการอัปโหลดไฟล์',
                    default => 'PHP รับไฟล์ไม่สำเร็จ',
                };
                \Illuminate\Support\Facades\Log::warning('Teacher document upload failed', [
                    'upload_error' => $code,
                    'php_ini' => php_ini_loaded_file(),
                    'upload_max_filesize' => ini_get('upload_max_filesize'),
                    'post_max_size' => ini_get('post_max_size'),
                    'upload_tmp_dir' => ini_get('upload_tmp_dir'),
                    'system_tmp_dir' => sys_get_temp_dir(),
                ]);
                throw ValidationException::withMessages([
                    'documents.' . $index => $message . ' [UPLOAD_ERR=' . $code . ']',
                ]);
            }
        }
        $existing = $request->user()->teacherApplication()->first();

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'bio' => ['required', 'string', 'max:3000'],
            'teaching_experience' => ['required', 'string', 'max:5000'],
            'teaching_languages' => ['required', 'array', 'min:1', 'max:10'],
            'teaching_languages.*' => ['required', 'string', 'distinct', Rule::in(Language::pluck('name')->all())],
            'document_type' => ['required', 'in:qualification,certificate,experience,portfolio'],
            'documents' => ['nullable', 'array', 'min:1', 'max:3'],
            'remove_document_ids' => ['sometimes', 'array', 'max:10'],
            'remove_document_ids.*' => ['required', 'integer', 'distinct'],
            'documents.*' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);
        $newPaths = [];
        $removedPaths = [];
        try {
            DB::transaction(function () use ($request, $validated, &$newPaths, &$removedPaths) {
                // Lock the applicant account to serialize duplicate submissions.
                $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
                abort_unless($user->role === 'user', 403);
                $application = $user->teacherApplication()->lockForUpdate()->first();
                if ($application && !in_array($application->status, [TeacherApplication::REJECTED, TeacherApplication::NEEDS_MORE_INFO], true)) {
                    throw ValidationException::withMessages(['application' => 'ใบสมัครนี้ส่งแล้ว กรุณารอผลพิจารณา']);
                }
                $files = $request->file('documents', []) ?? [];
                $removeIds = array_map('intval', $validated['remove_document_ids'] ?? []);
                $existingDocuments = $application ? $application->documents()->lockForUpdate()->get() : collect();
                $ownedIds = $existingDocuments->pluck('id')->map(fn ($id) => (int) $id)->all();
                if (array_diff($removeIds, $ownedIds) !== []) {
                    throw ValidationException::withMessages(['remove_document_ids' => 'เลือกได้เฉพาะเอกสารในใบสมัครของคุณ']);
                }
                $keptDocuments = $existingDocuments->reject(fn ($document) => in_array((int) $document->id, $removeIds, true));
                $total = $keptDocuments->count() + count($files);
                if ($total === 0) {
                    throw ValidationException::withMessages(['documents' => 'ต้องมีเอกสารประกอบอย่างน้อยหนึ่งไฟล์ กรุณาแนบไฟล์ทดแทน']);
                }
                if ($total > 10) {
                    throw ValidationException::withMessages(['documents' => 'ใบสมัครแนบเอกสารรวมได้ไม่เกิน 10 ไฟล์ ให้เลือกนำเอกสารเดิมออกก่อนเพิ่ม']);
                }
                if ($keptDocuments->contains(fn ($document) => !Storage::disk('teacher_documents')->exists($document->file_path))) {
                    throw ValidationException::withMessages(['documents' => 'มีเอกสารเดิมที่ไฟล์หาย กรุณาเลือกนำรายการนั้นออกและแนบไฟล์ทดแทน']);
                }
                foreach ($existingDocuments as $document) {
                    if (in_array((int) $document->id, $removeIds, true)) {
                        $removedPaths[] = $document->file_path;
                        $document->delete();
                    }
                }
                $fromStatus = $application?->status;
                $application ??= new TeacherApplication;
                $application->fill([
                    'user_id' => $user->id, 'full_name' => $validated['full_name'],
                    'bio' => $validated['bio'], 'teaching_experience' => $validated['teaching_experience'],
                    'teaching_languages' => $validated['teaching_languages'], 'submitted_at' => now(),
                ]);
                // Never accept role, status, approval or reviewer fields from the form.
                $application->forceFill([
                    'status' => TeacherApplication::PENDING, 'approved_at' => null,
                    'reviewed_by' => null, 'reviewed_at' => null, 'review_note' => null,
                ])->save();
                foreach ($files as $file) {
                    $path = $file->store((string) $user->id, 'teacher_documents');
                    $newPaths[] = $path;
                    $name = basename(str_replace('\\', '/', $file->getClientOriginalName()));
                    $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name) ?: 'document';
                    $application->documents()->create([
                        'document_type' => $validated['document_type'], 'file_path' => $path,
                        'original_name' => mb_substr($name, 0, 255),
                        'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                        'file_size' => $file->getSize(), 'uploaded_at' => now(),
                    ]);
                }
                if ($fromStatus !== null) {
                    $application->reviews()->create([
                        'reviewer_id' => null, 'from_status' => $fromStatus,
                        'to_status' => TeacherApplication::PENDING, 'note' => 'ผู้สมัครแก้ไขและส่งพิจารณาใหม่; นำเอกสารเดิมออก ' . count($removeIds) . ' ไฟล์; เพิ่มเอกสาร ' . count($files) . ' ไฟล์',
                    ]);
                }
            });
        } catch (Throwable $error) {
            if ($newPaths !== []) {
                Storage::disk('teacher_documents')->delete($newPaths);
            }
            throw $error;
        }
        // Remove physical files only after the database transaction succeeds.
        // Cleanup failure must not roll back or delete newly committed uploads.
        foreach ($removedPaths as $path) {
            try {
                if (!Storage::disk('teacher_documents')->delete($path)) {
                    \Illuminate\Support\Facades\Log::warning('Teacher document cleanup deferred');
                }
            } catch (Throwable $error) {
                \Illuminate\Support\Facades\Log::warning('Teacher document cleanup deferred', ['exception' => get_class($error)]);
            }
        }
        return redirect()->route('teacher.application.show')->with('status', 'ส่งใบสมัครแล้ว ระหว่างรออนุมัติคุณยังเรียนภาษาได้');
    }

    public function download(Request $request, TeacherDocument $document)
    {
        $ownerId = $document->application->user_id;
        abort_unless($request->user()->isAdmin() || (int) $request->user()->id === (int) $ownerId, 404);
        $disk = Storage::disk('teacher_documents');
        abort_unless($disk->exists($document->file_path), 404);
        return $disk->download($document->file_path, $document->original_name, [
            'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
