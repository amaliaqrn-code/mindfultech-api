<?php

namespace App\Services;

use App\Models\Streak;
use Carbon\Carbon;

class StreakService
{
    public function update($userId)
    {
        $today = Carbon::today();

        $streak = Streak::firstOrCreate(
            ['user_id' => $userId],
            [
                'current_streak' => 0,
                'longest_streak' => 0,
                'last_active_date' => $today
            ]
        );

        // 🔥 lanjut streak
        if ($streak->last_active_date == $today->copy()->subDay()) {
            $streak->current_streak += 1;
        }
        // 🟢 hari baru
        elseif ($streak->last_active_date != $today) {
            $streak->current_streak = 1;
        }

        // 🏆 longest
        if ($streak->current_streak > $streak->longest_streak) {
            $streak->longest_streak = $streak->current_streak;
        }

        $streak->last_active_date = $today;
        $streak->save();
    }

    public function reset($userId)
    {
        $streak = Streak::firstOrCreate(
            ['user_id' => $userId],
            [
                'current_streak' => 0,
                'longest_streak' => 0,
                'last_active_date' => Carbon::today()
            ]
        );

        $streak->current_streak = 0;
        $streak->save();
    }
}
