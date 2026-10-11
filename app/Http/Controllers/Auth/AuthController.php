<?php

namespace App\Http\Controllers\Auth;

use App\Enums\LearnerProficiency;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    /**
     * แสดงหน้าสมัครสมาชิก
     */
    public function showRegister()
    {
        return view('auth.register');
    }


    /**
     * รับข้อมูลสมัครสมาชิกและบันทึกลง database
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'account_type' => [
                'sometimes',
                'required',
                'in:learner,teacher_applicant',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'min:6',
                'confirmed',
            ],

            'learner_proficiency' => [
                'sometimes',
                'required',
                Rule::in([
                    ...array_column(LearnerProficiency::cases(), 'value'),
                    'not_sure',
                ]),
            ],
        ]);

        $selection = $validated['learner_proficiency']
            ?? LearnerProficiency::Beginner->value;

        $learnerProficiency = $selection === 'not_sure'
            ? null
            : $selection;

        $placementRequired = $selection === 'not_sure';

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'user',
            'learner_proficiency' => $learnerProficiency,
            'placement_required' => $placementRequired,
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        // Preserve Teacher Verification flow from PR #47.
        if (($validated['account_type'] ?? 'learner') === 'teacher_applicant') {
            return redirect()->route('teacher.application.show');
        }

        // Learners who are unsure of their level take placement first.
        if ($user->placement_required) {
            return redirect()->route('placement.show');
        }

        return redirect('/languages');
    }


    /**
     * แสดงหน้า Login
     */
    public function showLogin()
    {
        return view('auth.login');
    }


    /**
     * ตรวจสอบ Login
     */
    public function login(Request $request)
    {
        // ตรวจสอบรูปแบบข้อมูล
        $credentials = $request->validate([
            'email' => [
                'required',
                'email',
            ],

            'password' => [
                'required',
            ],
        ]);


        // ตรวจ email + password
        if (Auth::attempt(
            $credentials,
            $request->boolean('remember')
        )) {

            // เปลี่ยน Session ID หลัง Login สำเร็จ
            $request->session()->regenerate();


            // ถ้าก่อนหน้านี้ผู้ใช้พยายามเข้า page ที่ต้อง Login
            // Laravel จะพากลับไป page นั้น
            // ถ้าไม่มี จะไป Profile
                        return redirect()->intended('/languages');
        }


        // Login ไม่สำเร็จ
        return back()
            ->withErrors([
                'email' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง',
            ])
            ->onlyInput('email');
    }


    /**
     * แสดง Profile ของ User ที่กำลัง Login
     */
    public function profile()
    {
        return view('auth.profile', [
            'user' => Auth::user(),
        ]);
    }


    /**
     * Logout
     */
    public function logout(Request $request)
    {
        // เอา User ออกจากสถานะ Login
        Auth::logout();


        // ทำลาย Session เดิม
        $request->session()->invalidate();


        // สร้าง CSRF Token ใหม่
        $request->session()->regenerateToken();


        // กลับหน้า Login
        return redirect()->route('login');
    }
}
