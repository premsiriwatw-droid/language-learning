<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก</title>
</head>
<body>

    <h1>สมัครสมาชิก</h1>

    @if ($errors->any())
        <div>
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('register.store') }}" method="POST">
        @csrf

        <div>
            <label for="name">ชื่อ</label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name') }}"
                required
            >
        </div>

        <br>

        <div>
            <label for="email">อีเมล</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                required
            >
        </div>

        <br>

        <div>
            <label for="password">รหัสผ่าน</label>
            <input
                type="password"
                id="password"
                name="password"
                required
            >
        </div>

        <br>

        <div>
            <label for="password_confirmation">
                ยืนยันรหัสผ่าน
            </label>

            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                required
            >
        </div>

        <br>

        <button type="submit">
            สมัครสมาชิก
        </button>
    </form>

    <p>
        มีบัญชีแล้ว?
        <a href="{{ route('login') }}">
            เข้าสู่ระบบ
        </a>
    </p>

</body>
</html>