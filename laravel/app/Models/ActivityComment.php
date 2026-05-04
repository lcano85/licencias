<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ActivityComment extends Model {
    use HasFactory;

    protected $table = 'activity_comment';
    protected $fillable = [
        'activityID', 'user_id', 'act_comment', 'commentTypes'
    ];

    public function creator() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
