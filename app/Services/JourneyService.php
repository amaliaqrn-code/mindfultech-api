<?php
namespace App\Services;

use App\Models\JourneyProgress;

class JourneyService
{
    public function update($userId)
    {
        $progress = JourneyProgress::firstOrCreate(
            ['user_id' => $userId],
            [
                'total_focus_days' => 0,
                'level' => 1
            ]
        );

        $progress->total_focus_days += 1;

        // contoh leveling tiap 5 hari
        $progress->level = floor($progress->total_focus_days / 5) + 1;

        $progress->save();
    }
}
