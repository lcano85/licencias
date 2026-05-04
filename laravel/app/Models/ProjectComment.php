<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ProjectComment extends Model {
    use HasFactory;

    protected $table = 'project_comment';
    protected $fillable = [
        'projectID', 'user_id', 'prj_comment', 'commentTypes'
    ];

    public function creator() {
        return $this->belongsTo(User::class, 'user_id');
    }
}

