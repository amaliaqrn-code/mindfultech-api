<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FocusSession extends Model
{
    protected $fillable = [
        'user_id',
        'task_id',
        'duration',
        'break_duration',
        'status',
        'emotion',
        'started_at',
        'ended_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
