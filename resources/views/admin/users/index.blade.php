<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการ Users</title>
</head>
<body>

    <h1>จัดการ Users</h1>

    @if(session('success'))
        <p>{{ session('success') }}</p>
    @endif

    <a href="{{ route('admin.dashboard') }}">
        ← กลับ Dashboard
    </a>

    <br><br>

    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>ID</th>
                <th>ชื่อ</th>
                <th>Email</th>
                <th>Role</th>
                <th>จัดการ</th>
            </tr>
        </thead>

        <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->role }}</td>
                    <td>
                        <a href="{{ route('admin.users.edit', $user) }}">
                            แก้ไข
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <br>

    {{ $users->links() }}

</body>
</html>