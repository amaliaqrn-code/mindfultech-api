<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{

    protected $fillable = [
            'user_id',
            'category_id', // 👈 TINGGAL TAMBAHKAN INI YANG BIKIN ERROR TADI!
            'title',
            'description',
            'difficulty',
            'deadline',
            'is_completed',
        ];
    public function user() {
        return $this->belongsTo(User::class);
    }

    public function category() {
        return $this->belongsTo(Category::class);
    }
}
