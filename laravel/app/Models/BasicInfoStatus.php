<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BasicInfoStatus extends Model {
    use HasFactory;

    protected $table = 'basic_info_status';
    protected $fillable = [
        'status_name', 'status'
    ];
}
