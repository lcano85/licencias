<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivitiesAttachment extends Model {
    use HasFactory;

    protected $table = 'activities_attachment';
    protected $fillable = [
        'activityID', 'attachment_file'
    ];
}
