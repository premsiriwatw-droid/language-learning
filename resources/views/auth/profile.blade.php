<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>โปรไฟล์</title>
</head>

<body>

    <h1>โปรไฟล์ผู้ใช้</h1>

    <p>
        <strong>ชื่อ:</strong>
        {{ $user->name }}
    </p>

    <p>
        <strong>อีเมล:</strong>
        {{ $user->email }}
    </p>


    <form action="{{ route('logout') }}" method="POST">

        @csrf

        <button type="submit">
            ออกจากระบบ
        </button>

    </form>

</body>
</html>