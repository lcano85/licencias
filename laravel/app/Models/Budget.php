<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model {
    use HasFactory;

    protected $table = 'budget';
    protected $fillable = [
        'license_id', 
        'commercialID', 'user_type', 'company', 'commercialName', 'concept', 'subTotal', 'vat', 'total', 'condition', 'status', 'begin_month', 'begin_year', 'finish_month', 'finish_year', 'annual_value', 'total_months', 'monthly_value', 'license_pdf_path', 'license_pdf', 'category', 'subcategory', 'created_by', 'licensedConcept', 'licensedEnvironment', 'billing_frequency', 'budget_month', 'budget_year'
    ];

    public function client() {
        return $this->belongsTo(Clients::class, 'commercialID');
    }

    public function invoices() {
        return $this->hasMany(RegisterInvoice::class, 'budgetID');
    }

    public function license() {
        return $this->belongsTo(LicensesAgreements::class, foreignKey: 'license_id');
    }
}
