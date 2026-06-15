<?php

namespace App\Http\Controllers;

use App\Models\FocusSession;
use App\Services\StreakService;
use App\Services\JourneyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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
        // PERBAIKAN: Jika aplikasi Flutter bisa fokus tanpa task, buat task_id jadi nullable
        $request->validate([
            'task_id' => 'nullable|exists:tasks,id',
            'duration' => 'required|integer' // dalam satuan detik atau menit (asumsi kita: detik)
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

    public function finish(Request $request, $id)
    {
        $userId = Auth::user()->id;
        $user = $request->user();

        $session = FocusSession::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        // 1. Update status sesi ini menjadi selesai
        $session->update([
            'status' => 'finished',
            'ended_at' => now()
        ]);

        // 2. Tandai task selesai jika sesi ini terikat ke sebuah task
        if ($session->task) {
            $session->task->update([
                'is_completed' => true
            ]);
        }

        // 3. LOGIKA UTAMA: Hitung total durasi fokus sukses user HARI INI (dalam detik)
        // 5 menit = 300 detik. (Jika Flutter mengirim durasi dalam menit, ganti 300 menjadi 5)
        $totalDurationToday = $user->focusSessions()
            ->where('status', 'finished')
            ->whereDate('ended_at', Carbon::today())
            ->sum('duration');

        $isStreakUpdated = false;
        $isJourneyUpdated = false;

        // 4. Jika total fokus hari ini BARU SAJA menyentuh atau melewati 5 menit (300 detik)
        // Kita kunci agar proses ini hanya berjalan SEKALI dalam sehari
        if ($totalDurationToday >= 300) {

            // Cek apakah hari ini sudah ada sesi lain yang mengaktifkan streak?
            // Kita hitung total sesi sukses hari ini sebelum sesi yang sekarang
            $previousSessionsCount = $user->focusSessions()
                ->where('status', 'finished')
                ->whereDate('ended_at', Carbon::today())
                ->where('id', '!=', $session->id)
                ->count();

            // Sesi terdahulu belum ada atau belum mencapai target, berarti ini sesi pertama yang sukses mencapai 5 menit hari ini!
            if ($previousSessionsCount === 0 || ($totalDurationToday - $session->duration < 300)) {
                // Update streak harian (+1 streak)
                $this->streakService->update($userId);

                // Gerakkan Awan Mindy ke bubble berikutnya (+1 hari di journey)
                $this->journeyService->update($userId);

                $isStreakUpdated = true;
                $isJourneyUpdated = true;
            }
        }

        return response()->json([
            'message' => 'Focus session completed 🎉',
            'streak_and_journey_moved' => $isStreakUpdated, // Beritahu Flutter apakah Mindy bergerak atau tidak
            'total_duration_today_seconds' => $totalDurationToday,
            'data' => $session
        ]);
    }

    public function fail(Request $request, $id)
    {
        $userId = Auth::user()->id;

        $session = FocusSession::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $session->update([
            'status' => 'interrupted',
            'ended_at' => now()
        ]);

        // Sesuai request: Gagal sekali, langsung reset streak ke 0 tanpa kompromi!
        $this->streakService->reset($userId);

        return response()->json([
            'message' => 'Focus session failed & Streak reset dari nol! 💔',
            'data' => $session
        ]);
    }

    public function sync(Request $request)
    {
        $request->validate([
            'duration' => 'required|integer',
            'task_id' => 'nullable|exists:tasks,id',
            'emotion' => 'nullable|string|max:50',
            'day_number' => 'nullable|integer',
        ]);

        $user = $request->user();

        // Buat focus session dengan status finished + emotion
        $session = $user->focusSessions()->create([
            'task_id' => $request->task_id,
            'duration' => $request->duration,
            'status' => 'finished',
            'emotion' => $request->emotion,
            'started_at' => now()->subSeconds($request->duration),
            'ended_at' => now(),
        ]);

        // Tandai task selesai jika ada
        if ($session->task) {
            $session->task->update(['is_completed' => true]);
        }

        // Hitung total durasi fokus hari ini
        $totalDurationToday = $user->focusSessions()
            ->where('status', 'finished')
            ->whereDate('ended_at', Carbon::today())
            ->sum('duration');

        $isStreakUpdated = false;
        $isJourneyUpdated = false;

        // Update streak + journey jika total >= 300 detik (5 menit)
        if ($totalDurationToday >= 300) {
            $previousSessions = $user->focusSessions()
                ->where('status', 'finished')
                ->whereDate('ended_at', Carbon::today())
                ->where('id', '!=', $session->id)
                ->count();

            if ($previousSessions === 0 || ($totalDurationToday - $session->duration < 300)) {
                $this->streakService->update($user->id);
                $this->journeyService->update($user->id);
                $isStreakUpdated = true;
                $isJourneyUpdated = true;
            }
        }

        // Ambil data journey + streak terbaru dari server
        $journeyData = $this->journeyService->getJourneyData($user->id);
        $streak = $user->streak;

        return response()->json([
            'message' => 'Focus session synced successfully',
            'session' => $session,
            'journey' => $journeyData,
            'streak' => $streak ? [
                'current_streak' => $streak->current_streak,
                'longest_streak' => $streak->longest_streak,
                'last_active_date' => $streak->last_active_date,
            ] : null,
        ]);
    }
}
