<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ</title>
</head>

<body>

    <h1>เข้าสู่ระบบ</h1>

    @if ($errors->any())
        <div>
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif


    <form action="{{ route('login.authenticate') }}" method="POST">

        @csrf

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
            <label>
                <input
                    type="checkbox"
                    name="remember"
                >

                จดจำฉัน
            </label>
        </div>

        <br>

        <button type="submit">
            เข้าสู่ระบบ
        </button>

    </form>


    <p>
        ยังไม่มีบัญชี?

        <a href="{{ route('register') }}">
            สมัครสมาชิก
        </a>
    </p>

</body>
</html>