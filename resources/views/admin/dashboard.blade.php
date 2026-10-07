<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
</head>
<body>

    <h1>Admin Dashboard</h1>

    <p>
        ยินดีต้อนรับ {{ auth()->user()->name }}
    </p>

    <hr>

    <h2>ข้อมูลระบบ</h2>

    <p>
        จำนวนผู้ใช้ทั้งหมด:
        <strong>{{ $userCount }}</strong>
    </p>

    <p>
        จำนวน Admin:
        <strong>{{ $adminCount }}</strong>
    </p>

    <hr>

    <a href="{{ route('admin.users.index') }}">
        จัดการ Users
    </a>

    <br><br>

    <a href="{{ route('profile') }}">
        กลับ Profile
    </a>

</body>
</html>