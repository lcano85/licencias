<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingList extends Model {
    use HasFactory;

    protected $table = 'billing_list';
    protected $fillable = [
        'invoiceID', 'commercialID', 'user_type', 'company', 'commercialName', 'concept', 'invoiceNumber', 'invoiceDate', 'periodPaid', 'paidPeriod', 'criterion', 'subTotal', 'vat', 'total', 'balance', 'supportingDocument', 'documentDetail', 'licensedConcept', 'licensedEnvironment', 'createdBy'
    ];
}
