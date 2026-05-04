<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceConsecutive extends Model {
    use HasFactory;

    protected $table = 'invoice_consecutive';
    protected $fillable = [
        'consecutive_name',
        'next_number',
        'status'
    ];
}
