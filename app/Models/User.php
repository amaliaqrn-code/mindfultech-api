<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'gender',
        'phone',
        'image_path',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::created(function ($user) {

            $now = now();

            DB::table('categories')->insert([
                ['user_id' => $user->id, 'name' => 'Belajar', 'created_at' => $now, 'updated_at' => $now],
                ['user_id' => $user->id, 'name' => 'Pekerjaan', 'created_at' => $now, 'updated_at' => $now],
                ['user_id' => $user->id, 'name' => 'Kesehatan', 'created_at' => $now, 'updated_at' => $now],
                ['user_id' => $user->id, 'name' => 'Pribadi', 'created_at' => $now, 'updated_at' => $now],
                ['user_id' => $user->id, 'name' => 'Rumah', 'created_at' => $now, 'updated_at' => $now],
                ['user_id' => $user->id, 'name' => 'Lainnya', 'created_at' => $now, 'updated_at' => $now],
            ]);

            DB::table('streaks')->insert([
                'user_id' => $user->id,
                'current_streak' => 0,
                'longest_streak' => 0,
                'last_active_date' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('journey_progress')->insert([
                'user_id' => $user->id,
                'total_focus_days' => 0,
                'level' => 1,
                'last_focus_date' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });
    }

    public function tasks() {
        return $this->hasMany(Task::class);
    }

    public function categories() {
        return $this->hasMany(Category::class);
    }

    public function energyLogs() {
        return $this->hasMany(EnergyLog::class);
    }

    public function focusSessions() {
        return $this->hasMany(FocusSession::class);
    }

    public function journeyProgress() {
        return $this->hasOne(JourneyProgress::class);
    }

    public function streak() {
        return $this->hasOne(Streak::class);
    }
}
