<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->validate(['filter' => ['nullable', Rule::in(['all', 'unread'])]])['filter'] ?? 'all';

        return view('notifications.index', [
            'filter' => $filter,
            'notifications' => $request->user()->notifications()
                ->when($filter === 'unread', fn ($query) => $query->whereNull('read_at'))
                ->orderByDesc('id')->paginate(20)->withQueryString(),
            'unreadCount' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function read(Request $request, string $notification): RedirectResponse
    {
        $request->user()->notifications()->whereKey($notification)->firstOrFail()->markAsRead();

        return to_route('notifications.index')->with('status', 'ทำเครื่องหมายว่าอ่านแล้ว');
    }

    public function unread(Request $request, string $notification): RedirectResponse
    {
        $request->user()->notifications()->whereKey($notification)->firstOrFail()->markAsUnread();

        return to_route('notifications.index')->with('status', 'ทำเครื่องหมายว่ายังไม่อ่าน');
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return to_route('notifications.index')->with('status', 'อ่านการแจ้งเตือนทั้งหมดแล้ว');
    }
}
