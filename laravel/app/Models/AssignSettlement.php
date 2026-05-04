<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignSettlement extends Model {
    use HasFactory;

    protected $table = 'assign_settlement';
    protected $fillable = [
        'role_id'
    ];
}
