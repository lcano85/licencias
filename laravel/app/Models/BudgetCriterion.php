<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetCriterion extends Model {
    use HasFactory;

    protected $table = 'budget_criterion';
    protected $fillable = [
        'criterion_name',
        'criterion_status',
    ];
}
