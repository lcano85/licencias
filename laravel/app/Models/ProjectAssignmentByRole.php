<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectAssignmentByRole extends Model {
    use HasFactory;

    protected $table = 'project_assignment_by_role';
    protected $fillable = [
        'role_id'
    ];
}
