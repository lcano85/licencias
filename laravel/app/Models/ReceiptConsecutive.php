<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceiptConsecutive extends Model {
    use HasFactory;

    protected $table = 'receipt_consecutive';

    protected $fillable = [
        'consecutive_name',
        'next_number',
        'status',
    ];
}

