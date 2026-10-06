<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#101012">
    <title>เข้าสู่ระบบ | Language Learning</title>
    <link rel="preload" href="{{ asset('fonts/alex-brush/AlexBrush-Regular.ttf') }}" as="font" type="font/ttf" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <script src="{{ asset('js/login.js') }}" defer></script>
</head>
<body class="login-page" data-scene="default">
    <a class="login-skip" href="#login-form">ข้ามไปเข้าสู่ระบบ</a>
    <div class="login-atmosphere" aria-hidden="true">
        <div class="city-scene city-scene-china">
            <img class="scene-image" src="{{ asset('images/login/china-night.png') }}" alt="" decoding="async">
            <div class="city-reflections"></div>
        </div>
        <div class="city-scene city-scene-england">
            <img class="scene-image" src="{{ asset('images/login/england-sunset.png') }}" alt="" decoding="async">
            <div class="sunset-glow"></div>
        </div>
        <div class="atmosphere-veil"></div>
        <div class="atmosphere-grain"></div>
    </div>

    <div class="login-frame">
        <header class="login-header">
            <div class="login-brand">
                <div class="book-stage" aria-hidden="true">
                    <div class="book-shadow"></div>
                    <div class="book" data-book>
                        <div class="book-back"></div>
                        <div class="book-pages"></div>
                        <div class="book-spine"></div>
                        <div class="book-leaf book-leaf-three"></div>
                        <div class="book-leaf book-leaf-two"></div>
                        <div class="book-leaf book-leaf-one"></div>
                        <div class="book-cover">
                            <div class="book-cover-face"><span class="book-monogram">Ll.</span><span class="book-cover-rule"></span><span class="book-volume">VOL. 01</span></div>
                            <div class="book-cover-inside"></div>
                        </div>
                    </div>
                </div>
                <span class="brand-name">Language Learning</span>
            </div>
            <span class="header-note"><span></span> A WORLD OF POSSIBILITIES</span>
        </header>

        <main class="login-main">
            <section class="login-story" aria-labelledby="story-heading">
                <p class="story-eyebrow"><span class="eyebrow-line"></span> YOUR NEXT CHAPTER</p>
                <h1 id="story-heading">Learn languages.<br><span>Open your world.</span></h1>
                <p class="story-description">ฝึกภาษาหาเมียฝรั่ง เพื่อหาจุดหมายชีวิต พิชิต Global</p>
                <div class="language-explorer">
                    <p class="explorer-label" id="explorer-hint">เลือกบรรยากาศ แล้วออกเดินทาง</p>
                    <div class="language-buttons" role="group" aria-label="เลือกฉากเมือง" aria-describedby="explorer-hint">
                        <button class="language-button" type="button" data-scene-trigger="england" aria-pressed="false">
                            <svg class="language-flag" viewBox="0 0 60 40" aria-hidden="true"><path fill="#253b72" d="M0 0h60v40H0z"></path><path stroke="#fff" stroke-width="9" d="m0 0 60 40M60 0 0 40"></path><path stroke="#d33d50" stroke-width="3" d="m0 0 60 40M60 0 0 40"></path><path stroke="#fff" stroke-width="13" d="M30 0v40M0 20h60"></path><path stroke="#d33d50" stroke-width="7" d="M30 0v40M0 20h60"></path></svg>
                            English <span class="language-arrow" aria-hidden="true">↗</span>
                        </button>
                        <button class="language-button" type="button" data-scene-trigger="china" aria-pressed="false">
                            <svg class="language-flag" viewBox="0 0 60 40" aria-hidden="true"><path fill="#df3545" d="M0 0h60v40H0z"></path><path fill="#ffe089" d="m12 5 2 6h6l-5 4 2 6-5-4-5 4 2-6-5-4h6zM25 4l1 2h2l-2 2 1 2-2-1-2 1 1-2-2-2h2zM31 10l1 2h2l-2 2 1 2-2-1-2 1 1-2-2-2h2zM31 20l1 2h2l-2 2 1 2-2-1-2 1 1-2-2-2h2zM25 26l1 2h2l-2 2 1 2-2-1-2 1 1-2-2-2h2z"></path></svg>
                            <span lang="zh">中文</span> <span class="language-arrow" aria-hidden="true">↗</span>
                        </button>
                    </div>
                </div>
                <div class="scene-caption" aria-hidden="true">
                    <span class="scene-dot"></span><span data-scene-name>ONE WORD. ENDLESS POSSIBILITIES.</span>
                </div>
            </section>

            <section class="login-card" aria-labelledby="login-heading">
                <div class="card-accent" aria-hidden="true"></div>
                <p class="card-eyebrow">LET'S BEGIN AGAIN</p>
                <h2 id="login-heading">ยินดีต้อนรับกลับ<span class="heading-dot">.</span></h2>
                <p class="login-subtitle">เข้าสู่ระบบเพื่อเรียนภาษาต่อ</p>
                @if ($errors->any())
                    <div class="error-box" id="login-errors" role="alert">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif
                <form id="login-form" action="{{ route('login.authenticate') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="email">อีเมล</label>
                        <div class="input-wrap">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="3"></rect><path d="m4 7 8 6 8-6"></path></svg>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="example@email.com" autocomplete="username" required @error('email') aria-invalid="true" aria-describedby="login-errors" @enderror>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password">รหัสผ่าน</label>
                        <div class="input-wrap">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="3"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"></path></svg>
                            <input type="password" id="password" name="password" placeholder="กรอกรหัสผ่าน" autocomplete="current-password" required @error('password') aria-invalid="true" aria-describedby="login-errors" @enderror>
                        </div>
                    </div>
                    <label class="remember"><input type="checkbox" name="remember" @checked(old('remember'))><span>จดจำฉัน</span></label>
                    <button type="submit" class="login-button"><span>เข้าสู่ระบบ</span><span aria-hidden="true">↗</span></button>
                </form>
                <p class="register-text">ยังไม่มีบัญชี? <a href="{{ route('register') }}">สมัครสมาชิก <span aria-hidden="true">↗</span></a></p>
                <div class="card-footer"><span class="card-footer-line"></span><span>THE WORLD IS WAITING FOR YOU</span><span class="card-footer-line"></span></div>
            </section>
        </main>
        <footer class="login-footer">
            <p>Every word is a new beginning.</p>
            <button class="motion-toggle" type="button" data-motion-toggle aria-pressed="false" hidden><span data-motion-icon aria-hidden="true">Ⅱ</span><span data-motion-label>หยุดภาพเคลื่อนไหว</span></button>
            <span class="footer-edition" aria-hidden="true">EN / 中文</span>
        </footer>
    </div>
</body>
</html>
