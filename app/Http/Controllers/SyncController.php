<?php

namespace App\Http\Controllers;

use App\Models\JourneyProgress;
use App\Models\Streak;
use App\Services\JourneyService;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    protected $journeyService;

    public function __construct(JourneyService $journeyService)
    {
        $this->journeyService = $journeyService;
    }

    public function journey(Request $request)
    {
        $user = $request->user();
        $progress = JourneyProgress::firstOrCreate(
            ['user_id' => $user->id],
            ['total_focus_days' => 0, 'level' => 1, 'last_focus_date' => null]
        );
        $journeyData = $this->journeyService->getJourneyData($user->id);
        return response()->json(['message' => 'Journey progress berhasil diambil', 'data' => [
            'user_id' => $progress->user_id,
            'total_focus_days' => $progress->total_focus_days,
            'level' => $progress->level,
            'last_focus_date' => $progress->last_focus_date,
            'current_day_in_level' => $journeyData['current_day_in_level'],
            'updated_at' => $progress->updated_at,
        ]]);
    }

    public function streak(Request $request)
    {
        $user = $request->user();
        $streak = Streak::firstOrCreate(
            ['user_id' => $user->id],
            ['current_streak' => 0, 'longest_streak' => 0, 'last_active_date' => now()->toDateString()]
        );
        return response()->json(['message' => 'Streak data berhasil diambil', 'data' => [
            'user_id' => $streak->user_id,
            'current_streak' => $streak->current_streak,
            'longest_streak' => $streak->longest_streak,
            'last_active_date' => $streak->last_active_date,
            'updated_at' => $streak->updated_at,
        ]]);
    }
}
