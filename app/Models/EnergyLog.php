<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnergyLog extends Model
{
    protected $fillable = [
    'user_id',
    'energy_level'
];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
