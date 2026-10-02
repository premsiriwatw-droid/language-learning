<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
| Grant (or revoke with --revoke) Learning Structure admin access.
| is_admin is intentionally not mass-assignable, so this is the way to set it.
|   php artisan app:make-admin someone@example.com
*/
Artisan::command('app:make-admin {email} {--revoke}', function (string $email) {
    $user = \App\Models\User::where('email', $email)->first();

    if (! $user) {
        $this->error("ไม่พบผู้ใช้อีเมล {$email}");

        return 1;
    }

    $user->forceFill(['is_admin' => ! $this->option('revoke')])->save();

    $this->info($user->is_admin
        ? "{$email} เป็น Admin แล้ว"
        : "ถอนสิทธิ์ Admin ของ {$email} แล้ว");

    return 0;
})->purpose('Grant or revoke admin access for the Learning Structure screens');
