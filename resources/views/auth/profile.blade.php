<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>โปรไฟล์ | Language Learning</title>

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

        .profile-card {
            width: 520px;
            max-width: 92%;
            background: #ffffff;
            border-radius: 24px;
            padding: 45px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
        }

        .logo {
            text-align: center;
            font-size: 26px;
            font-weight: bold;
            color: #5f4bd8;
            margin-bottom: 25px;
        }

        .avatar {
            width: 90px;
            height: 90px;
            margin: 0 auto 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eeeaff;
            font-size: 42px;
        }

        h1 {
            text-align: center;
            font-size: 30px;
            color: #222;
            margin-bottom: 8px;
        }

        .subtitle {
            text-align: center;
            color: #777;
            margin-bottom: 30px;
        }

        .info-box {
            background: #f8f7ff;
            border-radius: 16px;
            padding: 22px;
            margin-bottom: 25px;
        }

        .info-row {
            margin-bottom: 18px;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .label {
            display: block;
            font-size: 13px;
            color: #777;
            margin-bottom: 5px;
        }

        .value {
            font-size: 17px;
            font-weight: bold;
            color: #333;
        }

        .logout-btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 10px;
            background: #6c5ce7;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .logout-btn:hover {
            background: #5946d2;
        }
    </style>
</head>

<body>

<div class="profile-card">

    <div class="logo">
        🌍 Language Learning
    </div>

    <div class="avatar">
        👤
    </div>

    <h1>โปรไฟล์ของฉัน</h1>

    <p class="subtitle">
        ข้อมูลบัญชีผู้ใช้งาน
    </p>

    <div class="info-box">

        <div class="info-row">
            <span class="label">ชื่อ</span>
            <div class="value">
                {{ $user->name }}
            </div>
        </div>

        <div class="info-row">
            <span class="label">อีเมล</span>
            <div class="value">
                {{ $user->email }}
            </div>
        </div>

    </div>

    <form action="{{ route('logout') }}" method="POST">
        @csrf

        <button type="submit" class="logout-btn">
            ออกจากระบบ
        </button>
    </form>

</div>

</body>
</html>