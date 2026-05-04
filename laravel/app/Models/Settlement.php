<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settlement extends Model {
    use HasFactory;

    protected $table = 'settlements';
    protected $fillable = [
        'settlement_no',
        'origin',
        'concept',
        'income_month_start',
        'income_month_end',
        'period_covered',
        'distribution_formula',
        'total_to_distribute',
        'amount_to_pay',
        'distribution_type',
        'distribution_data',
        'associates_data',
        'status',
        'paid_date',
        'distribution_id',
        'created_by',
    ];

    protected $casts = [
        'distribution_data' => 'array',
        'associates_data' => 'array',
        'income_month_start' => 'date',
        'income_month_end' => 'date',
        'paid_date' => 'date',
        'total_to_distribute' => 'decimal:2',
        'amount_to_pay' => 'decimal:2',
    ];

    public function distribution() {
        return $this->belongsTo(Distribution::class);
    }

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function associates() {
        return $this->belongsToMany(User::class, 'settlement_associates', 'settlement_id', 'associate_id')
            ->withPivot('percentage', 'fixed_amount', 'calculated_amount', 'status', 'paid_date')
            ->withTimestamps();
    }
}
