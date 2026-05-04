<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ProjectCommentDocument extends Model {
    use HasFactory;

    protected $table = 'project_comment_shared_documnet';
    protected $fillable = [
        'projectID', 'projectCommentID', 'attachment_file'
    ];
}
