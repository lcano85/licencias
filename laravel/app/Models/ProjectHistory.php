<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectHistory extends Model {

    use HasFactory;

    protected $table = 'project_histories';
    protected $fillable = [
        'project_id', 'user_id', 'action', 'changes'
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function project() {
        return $this->belongsTo(Projects::class, 'project_id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
