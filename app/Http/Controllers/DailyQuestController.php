<?php

namespace App\Http\Controllers;

use App\Services\Progress\DailyQuestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DailyQuestController extends Controller
{
    public function index(Request $request, DailyQuestService $quests): View
    {
        return view('frontend.daily-quests', ['quests' => $quests->today($request->user())]);
    }

    public function claim(Request $request, int $questId, DailyQuestService $quests): RedirectResponse
    {
        $quests->claim($request->user(), $questId);

        return redirect()->route('daily-quests.index')->with('status', 'รับรางวัลภารกิจแล้ว');
    }
}
