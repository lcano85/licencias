<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortfolioComment extends Model {
    use HasFactory;
    protected $table = 'portfolio_comments';

    protected $fillable = [
        'client_id',
        'invoice_id',
        'period_month',
        'period_year',
        'comment',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'rejection_reason'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function client() {
        return $this->belongsTo(Clients::class, 'client_id');
    }

    public function invoice() {
        return $this->belongsTo(RegisterInvoice::class, 'invoice_id');
    }

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver() {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeForPeriod($query, $month, $year) {
        return $query->where('period_month', $month)->where('period_year', $year);
    }

    public function scopePending($query) {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query) {
        return $query->where('status', 'approved');
    }

    public function canBeModified() {
        return $this->status === 'pending';
    }

    public function canBeApproved($user) {
        return $user->hasRole(['admin', 'Admin', 'master admin', 'Master Admin']) && $this->status === 'pending';
    }
}
