<?php

namespace App\Http\Controllers;

use App\Models\FocusSession;
use App\Services\StreakService;
use App\Services\JourneyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FocusSessionController extends Controller
{
    protected $streakService;
    protected $journeyService;

    public function __construct(StreakService $streakService, JourneyService $journeyService)
    {
        $this->streakService = $streakService;
        $this->journeyService = $journeyService;
    }

    public function start(Request $request)
    {
        $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'duration' => 'required|integer'
        ]);

        $session = $request->user()->focusSessions()->create([
            'task_id' => $request->task_id,
            'duration' => $request->duration,
            'break_duration' => $request->break_duration ?? 5,
            'status' => 'ongoing',
            'started_at' => now()
        ]);

        return response()->json([
            'message' => 'Focus session dimulai 🚀',
            'session' => $session
        ]);
    }

    public function finish($id)
    {
        $userId = Auth::user()->id;
        $session = FocusSession::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $session->update([
            'status' => 'finished',
            'ended_at' => now()
        ]);

        //tandai task selesai
        $session->task->update([
            'is_completed' => true
        ]);

        // update streak
        $this->streakService->update($userId);

        // update journey
        $this->journeyService->update($userId);

        return response()->json([
            'message' => 'Focus session completed 🎉',
            'data' => $session
        ]);
    }

    public function fail($id)
    {
        $userId = Auth::user()->id;

        $session = FocusSession::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $session->update([
            'status' => 'interrupted',
            'ended_at' => now()
        ]);

        // reset streak pakai service (biar konsisten)
        $this->streakService->reset($userId);

        return response()->json([
            'message' => 'Focus session failed 💔',
            'data' => $session
        ]);
    }
}
