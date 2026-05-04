<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Validation extends Model {
    use HasFactory;

    protected $table = 'validations';
    protected $fillable = [
        'report_type',
        'period_start',
        'period_end',
        'title',
        'concepts_data',
        'accountant_id',
        'accountant_status',
        'accountant_notes',
        'accountant_validated_at',
        'management_id',
        'management_status',
        'management_notes',
        'management_validated_at',
        'is_locked',
        'created_by'
    ];
    
    protected $casts = [
        'concepts_data' => 'array',
        'period_start' => 'date',
        'period_end' => 'date',
        'accountant_validated_at' => 'datetime',
        'management_validated_at' => 'datetime',
        'is_locked' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    // Relationships
    public function accountant() {
        return $this->belongsTo(User::class, 'accountant_id');
    }
    
    public function management() {
        return $this->belongsTo(User::class, 'management_id');
    }
    
    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function items() {
        return $this->hasMany(ValidationItem::class);
    }
    
    // Accessors
    public function getStatusAttribute() {
        if ($this->accountant_status === 'rejected') {
            return 'Rejected by Accountant';
        }
        
        if ($this->accountant_status === 'pending') {
            return 'Pending Accountant Validation';
        }
        
        if ($this->accountant_status === 'approved' && $this->management_status === 'pending') {
            return 'Pending Management Validation';
        }
        
        if ($this->accountant_status === 'approved' && $this->management_status === 'approved') {
            return 'Approved';
        }
        
        if ($this->management_status === 'rejected') {
            return 'Rejected by Management';
        }
        
        return 'Pending';
    }
    
    // Helper methods
    public function canBeModified() {
        return $this->accountant_status === 'pending' && !$this->is_locked;
    }
    
    public function canBeDeleted() {
        if ($this->accountant_status === 'approved' && $this->management_status === 'approved') {
            return false;
        }
        return !$this->is_locked;
    }
    
    public function isFullyApproved() {
        return $this->accountant_status === 'approved' && 
               $this->management_status === 'approved';
    }
    
    public function isRejected() {
        return $this->accountant_status === 'rejected' || 
               $this->management_status === 'rejected';
    }
}
