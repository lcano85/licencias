<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomeRecord extends Model {
    use HasFactory;

    protected $table = 'income_records';
    protected $fillable = [
        'mode',
        'bank_code',
        'company_id',
        'company',
        'commercial_name',
        'income_date',
        'income_amount',
        'other_amounts',
        'total_paid',
        'invoice_id',
        'invoice_number',
        'invoice_date',
        'concept',
        'invoice_period',
        'invoice_value',
        'balance',
        'rc_number',
        'rc_date',
        'created_by',
    ];
}
