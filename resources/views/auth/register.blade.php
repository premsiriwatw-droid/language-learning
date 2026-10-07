<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#fffdf9">
    <title>สมัครสมาชิก | Language Learning</title>
    <link rel="preload" href="{{ asset('fonts/bebas-neue/BebasNeue-Regular.ttf') }}" as="font" type="font/ttf" crossorigin>
    <link rel="preload" href="{{ asset('fonts/alex-brush/AlexBrush-Regular.ttf') }}" as="font" type="font/ttf" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
    <script src="{{ asset('js/login.js') }}" defer></script>
</head>
<body class="login-page register-page" data-scene="default">
    <a class="login-skip" href="#register-form">ข้ามไปสมัครสมาชิก</a>
    @include('auth.partials.atmosphere')
    <div class="register-breeze" aria-hidden="true">
        <svg viewBox="0 0 1440 1000" fill="none" preserveAspectRatio="xMidYMid slice">
            <path d="M-180 390C120 90 290 150 590 245S1120 470 1630 100"></path>
            <path d="M-200 750C220 1050 400 770 760 790S1280 710 1600 410"></path>
            <path d="M-180 415C160 140 310 185 590 275S1140 495 1630 130"></path>
        </svg>
    </div>

    <div class="login-frame register-frame">
        <header class="login-header">
            <div class="login-brand">
                @include('auth.partials.book')
                <span class="brand-name">Language Learning</span>
            </div>
            <span class="header-note"><span></span> A NEW CHAPTER STARTS HERE</span>
        </header>

        <main class="register-panels">
            <section class="login-story register-story" aria-labelledby="story-heading">
                <div class="card-accent" aria-hidden="true"></div>
                <p class="story-eyebrow"><span class="eyebrow-line"></span> YOUR FIRST CHAPTER</p>
                <h1 id="story-heading">Start learning.<br><span>Build your<br>future.</span></h1>
                <p class="story-description">เริ่มต้นเรียนภาษาอังกฤษและภาษาจีน<br>ฝึกคำศัพท์ ทำแบบฝึกหัด<br>และพัฒนาทักษะภาษาของคุณทุกวัน</p>
                @include('auth.partials.language-explorer')
                <div class="register-story-footer" aria-hidden="true">
                    <span>01 / THE BEGINNING</span><span class="story-footer-line"></span><span>EN + 中文</span>
                </div>
            </section>

            <section class="login-card register-card" aria-labelledby="register-heading">
                <div class="card-accent" aria-hidden="true"></div>
                <p class="card-eyebrow">CREATE YOUR ACCOUNT</p>
                <h2 id="register-heading">สร้างบัญชี<span class="heading-dot">.</span></h2>
                <p class="login-subtitle">สมัครสมาชิกเพื่อเริ่มเรียนภาษา</p>
                @if ($errors->any())
                    <div class="error-box" id="register-errors" role="alert">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form id="register-form" action="{{ route('register.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="name">ชื่อ</label>
                        <div class="input-wrap">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="8" r="4"></circle><path d="M4 21v-2a8 8 0 0 1 16 0v2"></path></svg>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="ชื่อของคุณ" autocomplete="name" maxlength="255" required @error('name') aria-invalid="true" aria-describedby="register-errors" @enderror>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email">อีเมล</label>
                        <div class="input-wrap">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="3"></rect><path d="m4 7 8 6 8-6"></path></svg>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="example@email.com" autocomplete="email" required @error('email') aria-invalid="true" aria-describedby="register-errors" @enderror>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password">รหัสผ่าน</label>
                        <div class="input-wrap">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="3"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"></path></svg>
                            <input type="password" id="password" name="password" placeholder="อย่างน้อย 6 ตัวอักษร" autocomplete="new-password" minlength="6" required @error('password') aria-invalid="true" aria-describedby="register-errors" @enderror>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation">ยืนยันรหัสผ่าน</label>
                        <div class="input-wrap">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="3"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3m1 6 2 2 4-4"></path></svg>
                            <input type="password" id="password_confirmation" name="password_confirmation" placeholder="กรอกรหัสผ่านอีกครั้ง" autocomplete="new-password" minlength="6" required @error('password') aria-invalid="true" aria-describedby="register-errors" @enderror>
                        </div>
                    </div>
                    <button type="submit" class="login-button"><span>สมัครสมาชิก</span><span aria-hidden="true">↗</span></button>
                </form>

                <p class="register-text">มีบัญชีแล้ว? <a href="{{ route('login') }}">เข้าสู่ระบบ <span aria-hidden="true">↗</span></a></p>
                <div class="card-footer"><span class="card-footer-line"></span><span>YOUR WORLD IS ABOUT TO GET BIGGER</span><span class="card-footer-line"></span></div>
            </section>
        </main>

        <footer class="login-footer">
            <p>Every great journey begins with a word.</p>
            <button class="motion-toggle" type="button" data-motion-toggle aria-pressed="false" hidden><span data-motion-icon aria-hidden="true">Ⅱ</span><span data-motion-label>หยุดภาพเคลื่อนไหว</span></button>
            <span class="footer-edition" aria-hidden="true">EN / 中文</span>
        </footer>
    </div>
</body>
</html>
