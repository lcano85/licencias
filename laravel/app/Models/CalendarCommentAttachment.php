<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class CalendarCommentAttachment extends Model {
    use HasFactory;

    protected $table = 'calendar_comment_attachment';
    protected $fillable = [
        'calendarID', 'calendarCommentID', 'attachment_file'
    ];
}
