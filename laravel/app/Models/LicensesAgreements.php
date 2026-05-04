<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LicensesAgreements extends Model
{
    use HasFactory;

    protected $table = 'licenses_agreements';

    protected $fillable = [
        'commercialID', 
        'commercialName', 
        'userType',          
        'licensedConcept',   
        'licensedEnvironment', 
        'startDate',         
        'endDate',           
        'billing_frequency', 
        'begin_month',       
        'begin_year',        
        'finish_month',      
        'finish_year',       
        'monthlyValue', 
        'annualValue', 
        'status',           
        'category', 
        'subcategory',
        'origin',          
        'created_by',
        'vat',
        'month_total_value',
    ];

    /**
     * Make Eloquent treat licensedEnvironment as a JSON array.
     * Exposes PHP array on read; stores JSON on write.
     */
    protected $casts = [
        'licensedEnvironment' => 'array',
        'startDate' => 'date',
        'endDate' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user() {
        return $this->belongsTo(User::class, foreignKey: 'created_by');
    }

    public function client() {
        return $this->belongsTo(Clients::class, 'commercialID');
    }

    public function attachments() {
        return $this->hasMany(LicenseAttachment::class, 'license_id');
    }

    public function budgets() {
        return $this->hasMany(Budget::class, 'license_id');
    }
}
