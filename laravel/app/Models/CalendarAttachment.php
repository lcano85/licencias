<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarAttachment extends Model {
    use HasFactory;

    protected $table = 'calendar_attachment';
    protected $fillable = [
        'calendarID', 'attachment_file'
    ];
}
