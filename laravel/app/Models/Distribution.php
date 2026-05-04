<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Distribution extends Model {
    use HasFactory;

    protected $table = 'distributions';
    protected $fillable = [
        'distribution_no',
        'origin',
        'concept',
        'distribution_date',
        'invoice_no',
        'rc_no',
        'base_value',
        'vat',
        'associate_subtotal',
        'admin_subtotal',
        'admin_vat',
        'admin_total',
        'total_to_pay',
        'balance',
        'status',
        'metadata',
        'validation_id',
        'income_id',
        'created_by',
    ];

    protected $casts = [
        'metadata' => 'array',
        'base_value' => 'decimal:2',
        'vat' => 'decimal:2',
        'associate_subtotal' => 'decimal:2',
        'admin_subtotal' => 'decimal:2',
        'admin_vat' => 'decimal:2',
        'admin_total' => 'decimal:2',
        'total_to_pay' => 'decimal:2',
        'balance' => 'decimal:2',
        'distribution_date' => 'date',
    ];

    public function validation() {
        return $this->belongsTo(Validation::class);
    }

    public function income() {
        return $this->belongsTo(IncomeRecord::class, 'income_id');
    }

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function settlements() {
        return $this->hasMany(Settlement::class);
    }
}
