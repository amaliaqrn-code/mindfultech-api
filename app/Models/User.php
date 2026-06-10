<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username', // 🟢 Izinkan mass assignment
        'gender',   // 🟢 Izinkan mass assignment
        'phone',    // 🟢 Izinkan mass assignment
        'image_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        // 🔴 Setiap kali ada User baru dibuat (Register)
        static::created(function (User $user) {

            // List 6 kategori default aplikasi kamu
            $now = now();
            $defaultCategories = [
                ['user_id' => $user->id, 'name' => 'Belajar', 'created_at' => $now, 'updated_at' => $now],
                ['user_id' => $user->id, 'name' => 'Pekerjaan', 'created_at' => $now, 'updated_at' => $now],
                ['user_id' => $user->id, 'name' => 'Kesehatan', 'created_at' => $now, 'updated_at' => $now],
                ['user_id' => $user->id, 'name' => 'Pribadi', 'created_at' => $now, 'updated_at' => $now],
                ['user_id' => $user->id, 'name' => 'Rumah', 'created_at' => $now, 'updated_at' => $now],
                ['user_id' => $user->id, 'name' => 'Lainnya', 'created_at' => $now, 'updated_at' => $now],
            ];

            DB::table('categories')->insert($defaultCategories);
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
    public function focusSessions()
    {
        return $this->hasMany(FocusSession::class);
    }
}
