<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#fffdf9">
    <title>@yield('title') | LangLearn</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}?v={{ filemtime(public_path('css/login.css')) }}">
    <script src="{{ asset('js/login.js') }}?v={{ filemtime(public_path('js/login.js')) }}" defer></script>
</head>
<body class="login-page @yield('page-class')" data-scene="default">
    <a class="login-skip" href="#auth-form">ข้ามไปกรอกข้อมูล</a>
    <div class="login-atmosphere" aria-hidden="true">
        <div class="city-scene city-scene-china">
            <img class="scene-image" src="{{ asset('images/login/china-sunrise.png') }}" alt="" decoding="async">
            <div class="sunrise-glow"></div><div class="city-reflections"></div>
        </div>
        <div class="city-scene city-scene-england">
            <img class="scene-image" src="{{ asset('images/login/england-sunset.png') }}" alt="" decoding="async">
            <div class="sunset-glow"></div>
        </div>
        <div class="atmosphere-veil"></div><div class="atmosphere-grain"></div>
    </div>
    <div class="login-frame">
        <main class="login-main">
            <x-auth-language-explorer />
            <section class="login-card" aria-labelledby="auth-heading">
                <div class="card-accent" aria-hidden="true"></div>
                @yield('form')
            </section>
        </main>
        <footer class="login-footer">
            <button class="motion-toggle" type="button" data-motion-toggle aria-pressed="false" hidden>
                <span data-motion-icon aria-hidden="true">Ⅱ</span><span data-motion-label>หยุดภาพเคลื่อนไหว</span>
            </button>
        </footer>
    </div>
</body>
</html>
