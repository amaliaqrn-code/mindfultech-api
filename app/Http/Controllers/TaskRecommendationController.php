<?php

namespace App\Http\Controllers;

use App\Services\TaskRecommendationService;
use App\Models\EnergyLog;
use Illuminate\Support\Facades\Auth;

class TaskRecommendationController extends Controller
{
    protected $taskService;

    public function __construct(TaskRecommendationService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function recommend()
    {
        $userId = Auth::user()->id;

        // 🔋 ambil energy terakhir
        $energy = EnergyLog::where('user_id', $userId)
            ->latest()
            ->value('energy_level');

        // fallback kalau belum ada energy
        if (!$energy) {
            $energy = 'medium';
        }

        // 🎯 ambil task
        $task = $this->taskService->getRecommendedTask($userId, $energy);

        if (!$task) {
            return response()->json([
                'message' => 'Tidak ada task tersedia 😴'
            ], 404);
        }

        return response()->json([
            'message' => 'Task rekomendasi untuk kamu ✨',
            'energy' => $energy,
            'task' => $task
        ]);
    }
}
