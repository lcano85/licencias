<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValidationItem extends Model {
    use HasFactory;

    protected $table = 'validation_items';
    protected $fillable = [
        'validation_id',
        'item_type',
        'item_id',
        'concept',
        'original_amount',
        'validated_amount',
        'accountant_status',
        'accountant_notes',
        'management_status',
        'management_notes'
    ];
    
    protected $casts = [
        'original_amount' => 'decimal:2',
        'validated_amount' => 'decimal:2',
    ];
    
    // Relationships
    public function validation() {
        return $this->belongsTo(Validation::class);
    }
    
    // Corrected relationships - remove the where clauses
    public function invoice() {
        return $this->belongsTo(RegisterInvoice::class, 'item_id');
        // Remove: ->where('item_type', 'invoice');
    }
    
    public function income() {
        return $this->belongsTo(IncomeRecord::class, 'item_id');
        // Remove: ->where('item_type', 'income');
    }
    
    public function budget() {
        return $this->belongsTo(Budget::class, 'item_id');
        // Remove: ->where('item_type', 'budget');
    }
    
    // Polymorphic-like accessor - This is correct
    public function getItemAttribute() {
        switch ($this->item_type) {
            case 'invoice':
                return $this->invoice;
            case 'income':
                return $this->income;
            case 'budget':
                return $this->budget;
            default:
                return null;
        }
    }
    
    // Helper methods
    public function getAmountDifference() {
        if ($this->validated_amount === null) {
            return 0;
        }
        return $this->validated_amount - $this->original_amount;
    }
    
    public function hasDiscrepancy() {
        return $this->validated_amount !== null && 
               $this->validated_amount != $this->original_amount;
    }
}