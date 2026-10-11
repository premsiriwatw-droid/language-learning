<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::orderBy('id')->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'role' => [
                'required',
                Rule::in(['user', 'admin', 'teacher']),
            ],
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($user, $validated) {
            $current = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            if ($validated['role'] === 'teacher') {
                // Eligibility is the approval record, not the current account role.
                $application = $current->teacherApplication()->lockForUpdate()->first();
                if (!$application || $application->status !== \App\Models\TeacherApplication::APPROVED || !$application->approved_at) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'role' => 'การให้สิทธิ์ครูต้องมีใบสมัครที่อนุมัติแล้ว',
                    ]);
                }
            }
            $current->update($validated);
        });

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'อัปเดตข้อมูล User เรียบร้อยแล้ว');
    }
}