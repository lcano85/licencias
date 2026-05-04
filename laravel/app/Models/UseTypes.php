<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UseTypes extends Model {
    use HasFactory;

    protected $table = 'use_types';
    protected $fillable = [
        'use_types_name', 'use_types_status'
    ];
}
