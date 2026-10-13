@extends('layouts.auth')

@section('title', 'เข้าสู่ระบบ')

@section('form')
    <p class="card-eyebrow">WELCOME BACK</p>
    <h1 id="auth-heading">ยินดีต้อนรับกลับ<span class="heading-dot">.</span></h1>
    <p class="login-subtitle">เข้าสู่ระบบเพื่อเรียนภาษาต่อ</p>
    @if ($errors->any())
        <div class="error-box" id="auth-errors" role="alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif
    <form id="auth-form" action="{{ route('login.authenticate') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="email">อีเมล</label>
            <div class="input-wrap">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="3"></rect><path d="m4 7 8 6 8-6"></path></svg>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="example@email.com" autocomplete="username" required @error('email') aria-invalid="true" aria-describedby="auth-errors" @enderror>
            </div>
        </div>
        <div class="form-group">
            <label for="password">รหัสผ่าน</label>
            <div class="input-wrap">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="3"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"></path></svg>
                <input type="password" id="password" name="password" placeholder="กรอกรหัสผ่าน" autocomplete="current-password" required @error('password') aria-invalid="true" aria-describedby="auth-errors" @enderror>
            </div>
        </div>
        <label class="remember"><input type="checkbox" name="remember" @checked(old('remember'))><span>จดจำฉัน</span></label>
        <button type="submit" class="login-button"><span>เข้าสู่ระบบ</span><span aria-hidden="true">↗</span></button>
    </form>
    <p class="register-text">ยังไม่มีบัญชี? <a href="{{ route('register') }}">สมัครสมาชิก <span aria-hidden="true">↗</span></a></p>
@endsection
