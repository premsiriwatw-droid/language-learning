<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApprovedTeacherMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isApprovedTeacher(), 403, 'เฉพาะผู้สอนที่ผ่านการอนุมัติแล้ว');
        return $next($request);
    }
}
