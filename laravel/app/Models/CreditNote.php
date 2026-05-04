<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditNote extends Model {
    use HasFactory;

    protected $table = 'credit_notes';
    protected $fillable = [
        'invoice_id',
        'cn_number',
        'cn_date',
        'reason',
        'concept',
        'period',
        'criterion',
        'subTotal',
        'vat',
        'total',
        'supporting_doc', // File path
        'created_by',
    ];
 
    public function invoice() {
        return $this->belongsTo(RegisterInvoice::class, 'invoice_id');
    }

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }
}
