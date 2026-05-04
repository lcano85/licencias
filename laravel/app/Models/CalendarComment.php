<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class CalendarComment extends Model {
    use HasFactory;

    protected $table = 'calendar_comment';
    protected $fillable = [
        'calendarID', 'user_id', 'act_comment'
    ];

    public function creator() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
