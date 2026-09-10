<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LangLearn - @yield('title')</title>
    <!-- โหลด Tailwind CSS สำหรับทดสอบ (ถ้าใช้ npm run dev ของ Laravel อยู่แล้ว ให้ใช้ @vite แทน) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Nunito', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    </style>
    <!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LangLearn - @yield('title')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- เพิ่ม Alpine.js สำหรับ UI Interactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Nunito', 'Segoe UI', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 pb-12">
    
    <nav class="bg-white border-b-2 border-gray-200 sticky top-0 z-10">
        <div class="max-w-4xl mx-auto px-4 h-16 flex items-center justify-between">
            <a href="/dev/languages" class="text-2xl font-extrabold text-emerald-500 tracking-tight hover:opacity-80 transition">
                LangLearn
            </a>
            
            <div class="flex items-center gap-4 font-bold text-gray-500">
                <div class="flex items-center gap-1 text-amber-500">
                    <span>🔥</span> <span>5</span>
                </div>
                <div class="flex items-center gap-1 text-blue-500">
                    <span>⚡</span> <span>120</span>
                </div>
                <div class="w-8 h-8 rounded-full bg-gray-200 border-2 border-gray-300"></div>
            </div>
        </div>
    </nav>

    <main class="max-w-3xl mx-auto mt-8 px-4">
        @yield('content')
    </main>

</body>
</html>
</head>
<body class="bg-gray-50 text-gray-800 pb-12">
    
    <!-- Navbar -->
    <nav class="bg-white border-b-2 border-gray-200 sticky top-0 z-10">
        <div class="max-w-4xl mx-auto px-4 h-16 flex items-center justify-between">
            <a href="/dev/languages" class="text-2xl font-extrabold text-emerald-500 tracking-tight hover:opacity-80 transition">
                LangLearn
            </a>
            
            <div class="flex items-center gap-4 font-bold text-gray-500">
                <div class="flex items-center gap-1 text-amber-500">
                    <span>🔥</span> <span>5</span>
                </div>
                <div class="flex items-center gap-1 text-blue-500">
                    <span>⚡</span> <span>{{ $userXp ?? 120 }}</span>
                </div>
                <!-- พื้นที่สำหรับ User Avatar (คนที่ 1 ทำ) -->
                <div class="w-8 h-8 rounded-full bg-gray-200 border-2 border-gray-300"></div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-3xl mx-auto mt-8 px-4">
        @yield('content')
    </main>

</body>
</html>