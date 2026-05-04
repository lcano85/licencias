<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegisterInvoice extends Model {
    use HasFactory;

    protected $table = 'register_invoice';
    protected $fillable = [
        'budgetID', 'invoiceConsecutive', 'periodPaid', 'criterion', 'created_by', 'invoiceNumber', 'invoiceDate', 'paidPeriod', 'licensedConcept', 'licensedEnvironment', 'commercialID', 'user_type', 'company', 'commercialName', 'subTotal', 'vat', 'total'
    ];

    public function cashReceipts() {
        return $this->hasMany(CashReceipt::class, 'invoice_id');
    }

    public function creditNotes() {
        return $this->hasMany(CreditNote::class, 'invoice_id');
    }

    public function budget() {
        return $this->belongsTo(Budget::class, 'budgetID');
    }
}
