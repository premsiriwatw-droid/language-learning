<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>LangLearn - @yield('title', 'เรียนภาษา')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script
        defer
        src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js">
    </script>

    <link rel="stylesheet" href="{{ asset('css/learner-profile.css') }}">

    <style>
        body {
            font-family: 'Nunito', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
    </style>

    @stack('styles')
</head>
<body class="bg-gray-50 text-gray-800 pb-12 @yield('body-class')">
    <a class="skip-link" href="#main-content">ข้ามไปเนื้อหา</a>
    <nav class="site-nav" aria-label="เมนูหลัก">
        <div class="site-nav-inner">
            <a href="{{ url('/languages') }}" class="site-brand" aria-label="LangLearn หน้าหลัก">
                <span aria-hidden="true" class="brand-mark">L</span>
                @if(request()->is('languages'))
                    <span class="brand-word" aria-hidden="true">@foreach(str_split('LangLearn') as $letter)<span class="brand-letter">{{ $letter }}</span>@endforeach</span>
                @else
                    LangLearn
                @endif
            </a>
            <div class="site-nav-links">

                <a
                    href="{{ route('languages.index') }}"
                    @if(request()->is('languages', 'courses/*', 'units/*', 'lessons/*'))
                        aria-current="page"
                    @endif
                >
                    บทเรียน
                </a>

                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.languages.index') }}"
                            @if(request()->is('admin/*')) aria-current="page" @endif>
                                Admin
                        </a>
                    @endif
                    <a href="{{ route('profile') }}" @if(request()->is('profile')) aria-current="page" @endif>โปรไฟล์</a>
                    <span class="nav-initial" aria-hidden="true">
                    @if(\Illuminate\Support\Facades\Storage::disk('local')->exists('profile-photos/'.auth()->id().'/avatar'))
                        <img src="{{ route('profile.photo') }}"
                            alt=""
                            width="34"
                            height="34"
                        >
                    @else
                        {{ mb_substr(auth()->user()->name, 0, 1) }}
                    @endif
                </span>

                <form class="logout-form" action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="logout-button" type="submit">
                        ออกจากระบบ
                    </button>
                </form>
                @else
                    <a href="{{ route('login') }}">
                        เข้าสู่ระบบ
                    </a>
                @endauth

            </div>
        </div>
    </nav>

    <main
        id="main-content"
        class="max-w-3xl mx-auto mt-8 px-4 site-main"
    >
        @yield('content')
    </main>

    @stack('scripts')

</body>
</html>