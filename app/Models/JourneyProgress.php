<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JourneyProgress extends Model
{
    protected $fillable = [
        'user_id',
        'total_focus_days',
        'level',
        'last_focus_date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
