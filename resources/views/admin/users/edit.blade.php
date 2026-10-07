<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไข User</title>
</head>
<body>

    <h1>แก้ไข User</h1>

    <form
        method="POST"
        action="{{ route('admin.users.update', $user) }}"
    >
        @csrf
        @method('PUT')

        <div>
            <label>ชื่อ</label>

            <input
                type="text"
                name="name"
                value="{{ old('name', $user->name) }}"
                required
            >
        </div>

        <br>

        <div>
            <label>Email</label>

            <input
                type="email"
                name="email"
                value="{{ old('email', $user->email) }}"
                required
            >
        </div>

        <br>

        <div>
            <label>Role</label>

            <select name="role" required>
                <option
                    value="user"
                    @selected(old('role', $user->role) === 'user')
                >
                    User
                </option>

                <option
                    value="admin"
                    @selected(old('role', $user->role) === 'admin')
                >
                    Admin
                </option>
            </select>
        </div>

        <br>

        <button type="submit">
            บันทึก
        </button>
    </form>

    <br>

    <a href="{{ route('admin.users.index') }}">
        ← กลับ
    </a>

</body>
</html>