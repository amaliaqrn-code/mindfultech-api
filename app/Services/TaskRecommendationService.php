<?php

namespace App\Services;

use App\Models\Task;

class TaskRecommendationService
{
    public function getRecommendedTask($userId, $energy)
    {
        $query = Task::where('user_id', $userId)
            ->where('is_completed', false);

        // 🔋 filter ringan pakai energy (opsional)
        if ($energy === 'low') {
            $query->where('difficulty', 'easy');
        } elseif ($energy === 'medium') {
            $query->whereIn('difficulty', ['easy', 'medium']);
        }

        // 🎯 PRIORITAS UTAMA → DEADLINE TERDEKAT
        $query->orderBy('deadline', 'asc');

        // kalau ada deadline null, dorong ke bawah
        $query->orderByRaw('deadline IS NULL');

        return $query->first();
    }
}
