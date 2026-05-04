<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityHistory extends Model {

    use HasFactory;

    protected $table = 'activity_histories';
    protected $fillable = [
        'activity_id', 'user_id', 'action', 'changes'
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function activity() {
        return $this->belongsTo(Activities::class, 'activity_id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
