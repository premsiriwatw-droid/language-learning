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

    <style>
        body {
            font-family: 'Nunito', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 pb-12">

    <!-- Navbar -->
    <nav class="bg-white border-b-2 border-gray-200 sticky top-0 z-10">
        <div class="max-w-4xl mx-auto px-4 h-16 flex items-center justify-between">

            <!-- Logo -->
            <a
                href="{{ route('languages.index') }}"
                class="text-2xl font-extrabold text-emerald-500 tracking-tight hover:opacity-80 transition"
            >
                LangLearn
            </a>

            <!-- User Status -->
            <div class="flex items-center gap-4 font-bold text-gray-500">

                <!-- Streak -->
                <div class="flex items-center gap-1 text-amber-500">
                    <span>🔥</span>
                    <span>5</span>
                </div>

                <!-- XP -->
                <div class="flex items-center gap-1 text-blue-500">
                    <span>⚡</span>
                    <span>{{ $userXp ?? 120 }}</span>
                </div>

                <!-- Profile -->
                @auth
                    <a
                        href="{{ route('profile') }}"
                        class="w-8 h-8 rounded-full bg-gray-200 border-2 border-gray-300 hover:border-emerald-400 transition"
                        title="โปรไฟล์"
                    ></a>
                @else
                    <div class="w-8 h-8 rounded-full bg-gray-200 border-2 border-gray-300"></div>
                @endauth

            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-3xl mx-auto mt-8 px-4">
        @yield('content')
    </main>

</body>
</html>