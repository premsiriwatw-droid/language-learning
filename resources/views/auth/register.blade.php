<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>สมัครสมาชิก | Language Learning</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #6c5ce7, #8e7dff);
        }

        .container {
            width: 900px;
            max-width: 95%;
            min-height: 590px;
            display: flex;
            background: #ffffff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
        }

        .left {
            width: 50%;
            padding: 55px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: linear-gradient(135deg, #5f4bd8, #7b68ee);
            color: white;
        }

        .logo {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 35px;
        }

        .left h1 {
            font-size: 42px;
            line-height: 1.2;
            margin-bottom: 18px;
        }

        .left p {
            font-size: 16px;
            line-height: 1.7;
            opacity: 0.9;
        }

        .languages {
            display: flex;
            gap: 12px;
            margin-top: 28px;
        }

        .language {
            padding: 10px 16px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.15);
        }

        .right {
            width: 50%;
            padding: 45px 55px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .right h2 {
            font-size: 30px;
            margin-bottom: 8px;
            color: #222;
        }

        .subtitle {
            color: #777;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-size: 14px;
            font-weight: bold;
            color: #444;
        }

        input {
            width: 100%;
            padding: 13px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 15px;
            outline: none;
        }

        input:focus {
            border-color: #6c5ce7;
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.1);
        }

        .register-btn {
            width: 100%;
            padding: 14px;
            margin-top: 5px;
            border: none;
            border-radius: 10px;
            background: #6c5ce7;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .register-btn:hover {
            background: #5946d2;
        }

        .login-text {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }

        .login-text a {
            color: #6c5ce7;
            font-weight: bold;
            text-decoration: none;
        }

        .error-box {
            padding: 12px;
            margin-bottom: 18px;
            border-radius: 10px;
            background: #ffecec;
            color: #d63031;
            font-size: 14px;
        }

        @media (max-width: 760px) {
            .left {
                display: none;
            }

            .right {
                width: 100%;
                padding: 35px 25px;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <div class="left">

        <div class="logo">
            🌍 Language Learning
        </div>

        <h1>
            Start learning.<br>
            Build your future.
        </h1>

        <p>
            เริ่มต้นเรียนภาษาอังกฤษและภาษาจีน
            ฝึกคำศัพท์ ทำแบบฝึกหัด
            และพัฒนาทักษะภาษาของคุณทุกวัน
        </p>

        <div class="languages">
            <div class="language">🇬🇧 English</div>
            <div class="language">🇨🇳 中文</div>
        </div>

    </div>


    <div class="right">

        <h2>สร้างบัญชี ✨</h2>

        <p class="subtitle">
            สมัครสมาชิกเพื่อเริ่มเรียนภาษา
        </p>


        @if ($errors->any())
            <div class="error-box">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif


        <form action="{{ route('register.store') }}" method="POST">

            @csrf

            <div class="form-group">
                <label for="name">ชื่อ</label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="ชื่อของคุณ"
                    required
                >
            </div>


            <div class="form-group">
                <label for="email">อีเมล</label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="example@email.com"
                    required
                >
            </div>


            <div class="form-group">
                <label for="password">รหัสผ่าน</label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="อย่างน้อย 6 ตัวอักษร"
                    required
                >
            </div>


            <div class="form-group">
                <label for="password_confirmation">
                    ยืนยันรหัสผ่าน
                </label>

                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    placeholder="กรอกรหัสผ่านอีกครั้ง"
                    required
                >
            </div>


            <button type="submit" class="register-btn">
                สมัครสมาชิก
            </button>

        </form>


        <div class="login-text">
            มีบัญชีแล้ว?
            <a href="{{ route('login') }}">
                เข้าสู่ระบบ
            </a>
        </div>

    </div>

</div>

</body>
</html>