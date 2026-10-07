<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        // ตรวจสอบข้อมูลที่ผู้ใช้กรอก
        $validated = $request->validate([
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
        ]);


        // สร้าง User ใหม่
        // User Model ของเรามี password => hashed อยู่แล้ว
        // Laravel จึง hash password ให้อัตโนมัติ
        $user = User::create($validated);


        // Login ให้อัตโนมัติหลังสมัครสมาชิก
        Auth::login($user);


        // สร้าง Session ID ใหม่เพื่อความปลอดภัย
        $request->session()->regenerate();


        // ตอนนี้ใช้ Profile เป็นหน้าหลัง Login ชั่วคราว
        // เพื่อไม่ไปสร้าง Home/Dashboard ชนกับคนที่ 5
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
