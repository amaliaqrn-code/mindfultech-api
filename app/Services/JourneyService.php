<?php

namespace App\Services;

use App\Models\JourneyProgress;
use Carbon\Carbon;

class JourneyService
{
    /**
     * Berdasarkan logika kamu: 1 Level = 6 Hari.
     * Level 1: Hari 1 - 6
     * Level 2: Hari 7 - 12
     * Level 3: Hari 13 - 18
     * Level 4: Hari 19 - 24
     * Level 5: Hari 25 - 30
     * Level 6: Hari 31+
     */
    private static array $levelThresholds = [
        6 => 31,
        5 => 25,
        4 => 19,
        3 => 13,
        2 => 7,
        1 => 1,
    ];

    public function update($userId): void
    {
        $today = Carbon::today()->toDateString();
        $progress = JourneyProgress::firstOrCreate(
            ['user_id' => $userId],
            ['total_focus_days' => 0, 'level' => 1, 'last_focus_date' => null]
        );

        if ($progress->last_focus_date === $today) {
            return;
        }

        $progress->total_focus_days += 1;
        $progress->level = $this->calculateLevel($progress->total_focus_days);
        $progress->last_focus_date = $today;
        $progress->save();
    }

    private function calculateLevel(int $totalDays): int
    {
        foreach (self::$levelThresholds as $level => $requiredDays) {
            if ($totalDays >= $requiredDays) {
                return $level;
            }
        }
        return 1;
    }

    /**
     * Tambahkan fungsi helper ini atau gunakan logika ini saat Flutter memanggil API /journey.
     * Tujuannya untuk menentukan posisi Bubble 1-6 si Awan Mindy di level saat ini.
     */
    public function getJourneyData($userId): array
    {
        $progress = JourneyProgress::where('user_id', $userId)->first();

        if (!$progress) {
            return [
                'total_focus_days' => 0,
                'level' => 1,
                'current_day_in_level' => 1 // Bubble 1
            ];
        }

        // RUMUS MENCARI BUBBLE (1 sampai 6)
        // Misal total_focus_days = 5 -> (5 - 1) % 6 + 1 = 5 (Bubble 5)
        // Misal total_focus_days = 6 -> (6 - 1) % 6 + 1 = 6 (Bubble 6) -> Waktunya levelResultPage!
        // Misal total_focus_days = 7 -> (7 - 1) % 6 + 1 = 1 (Balik ke Bubble 1 di Level 2)
        $currentDayInLevel = (($progress->total_focus_days - 1) % 6) + 1;

        return [
            'total_focus_days' => $progress->total_focus_days,
            'level' => $progress->level,
            'last_focus_date' => $progress->last_focus_date,
            'current_day_in_level' => $currentDayInLevel
        ];
    }
}
