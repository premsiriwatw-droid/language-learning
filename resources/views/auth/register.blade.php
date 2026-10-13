@extends('layouts.auth')

@section('title', 'สมัครสมาชิก')
@section('page-class', 'register-page')

@section('form')
    <p class="card-eyebrow">YOUR JOURNEY STARTS HERE</p>
    <h1 id="auth-heading">สร้างบัญชี<span class="heading-dot">.</span></h1>
    <p class="login-subtitle">สมัครสมาชิกเพื่อเริ่มเรียนภาษา</p>
    @if ($errors->any())
        <div class="error-box" id="auth-errors" role="alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif
    <form id="auth-form" action="{{ route('register.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="account_type">สมัครเพื่อ</label>
            <select id="account_type" name="account_type" required aria-describedby="account-type-hint{{ $errors->has('account_type') ? ' auth-errors' : '' }}" @error('account_type') aria-invalid="true" @enderror>
                <option value="learner" @selected(old('account_type', 'learner') === 'learner')>เรียนภาษา</option>
                <option value="teacher_applicant" @selected(old('account_type') === 'teacher_applicant')>ยื่นสมัครเป็นผู้สอน</option>
            </select>
            <p class="field-hint" id="account-type-hint">ผู้สมัครครูต้องส่งใบสมัครและรอ Admin อนุมัติ ระหว่างรอยังเรียนภาษาได้</p>
        </div>
        <div class="form-group">
            <label for="name">ชื่อ</label>
            <div class="input-wrap">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="8" r="4"></circle><path d="M4 21v-2a8 8 0 0 1 16 0v2"></path></svg>
                <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="ชื่อของคุณ" autocomplete="name" required @error('name') aria-invalid="true" aria-describedby="auth-errors" @enderror>
            </div>
        </div>
        <div class="form-group">
            <label for="email">อีเมล</label>
            <div class="input-wrap">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="3"></rect><path d="m4 7 8 6 8-6"></path></svg>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="example@email.com" autocomplete="email" required @error('email') aria-invalid="true" aria-describedby="auth-errors" @enderror>
            </div>
        </div>
        <div class="form-group">
            <label for="password">รหัสผ่าน</label>
            <div class="input-wrap">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="3"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"></path></svg>
                <input type="password" id="password" name="password" placeholder="อย่างน้อย 6 ตัวอักษร" autocomplete="new-password" minlength="6" required @error('password') aria-invalid="true" aria-describedby="auth-errors" @enderror>
            </div>
        </div>
        <div class="form-group">
            <label for="password_confirmation">ยืนยันรหัสผ่าน</label>
            <div class="input-wrap">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="3"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3m-7 5 2 2 3-3"></path></svg>
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="กรอกรหัสผ่านอีกครั้ง" autocomplete="new-password" minlength="6" required @error('password_confirmation') aria-invalid="true" aria-describedby="auth-errors" @enderror>
            </div>
        </div>
        <div class="form-group">
            <label for="learner_proficiency">Learner Proficiency Level</label>
            <select id="learner_proficiency" name="learner_proficiency" required aria-describedby="proficiency-hint{{ $errors->has('learner_proficiency') ? ' auth-errors' : '' }}" @error('learner_proficiency') aria-invalid="true" @enderror>
                @foreach (\App\Enums\LearnerProficiency::cases() as $level)
                    <option value="{{ $level->value }}" @selected(old('learner_proficiency', 'beginner') === $level->value)>{{ $level->label() }}</option>
                @endforeach
                <option value="not_sure" @selected(old('learner_proficiency') === 'not_sure')>Not sure</option>
            </select>
            <p class="field-hint" id="proficiency-hint">เลือก Not sure เพื่อทำแบบทดสอบแนะนำระดับหลังสมัคร</p>
        </div>
        <button type="submit" class="login-button"><span>สมัครสมาชิก</span><span aria-hidden="true">↗</span></button>
    </form>
    <p class="register-text">มีบัญชีแล้ว? <a href="{{ route('login') }}">เข้าสู่ระบบ <span aria-hidden="true">↗</span></a></p>
@endsection
