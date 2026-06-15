<?php

namespace App\Services;

use App\Models\Streak;
use Carbon\Carbon;

class StreakService
{
    public function update($userId)
    {
        $today = Carbon::today()->toDateString(); // Menggunakan string format 'YYYY-MM-DD' agar aman dibandingkannya

        // Ambil data streak, kalau belum ada buat baru
        // Kita set default last_active_date ke null agar terdeteksi sebagai pengguna baru
        $streak = Streak::firstOrCreate(
            ['user_id' => $userId],
            [
                'current_streak' => 0,
                'longest_streak' => 0,
                'last_active_date' => null
            ]
        );

        $lastActive = $streak->last_active_date ? Carbon::parse($streak->last_active_date)->toDateString() : null;
        $yesterday = Carbon::yesterday()->toDateString();

        // 1. Jika ini pengguna baru (belum pernah aktif) atau streaknya sempat reset
        if ($lastActive === null) {
            $streak->current_streak = 1;
        }
        // 2. 🔥 Lanjut streak (Terakhir aktif adalah KEMARIN)
        elseif ($lastActive === $yesterday) {
            $streak->current_streak += 1;
        }
        // 3. 🟢 Hari baru setelah bolong (Terakhir aktif sebelum kemarin, dan bukan hari ini)
        elseif ($lastActive !== $today) {
            $streak->current_streak = 1;
        }
        // Note: Kalau $lastActive === $today (dia fokus lagi di hari yang sama),
        // tidak akan mengubah current_streak (sesuai aturan harian kamu).

        // 🏆 Hitung rekor longest streak
        if ($streak->current_streak > $streak->longest_streak) {
            $streak->longest_streak = $streak->current_streak;
        }

        // Simpan tanggal aktif hari ini
        $streak->last_active_date = $today;
        $streak->save();
    }

    public function reset($userId)
    {
        $streak = Streak::where('user_id', $userId)->first();

        if ($streak) {
            $streak->current_streak = 0;
            $streak->save();
        }
    }
}
