<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashReceipt extends Model {
    use HasFactory;

    protected $table = 'cash_receipts';
    protected $fillable = [
        'invoice_id',
        'receipt_no',
        'receipt_date',
        'amount',
        'payment_method',
        'bank_code',
        'created_by',
        'note',
    ];

    public function invoice() {
        return $this->belongsTo(RegisterInvoice::class, 'invoice_id');
    }
}
