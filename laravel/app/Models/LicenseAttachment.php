<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LicenseAttachment extends Model {
    use HasFactory;
    protected $table = 'license_attachments';

    protected $fillable = [
        'license_id', 
        'original_name', 
        'file_name',          
        'file_path',   
        'file_type', 
        'file_size',         
        'description',           
        'uploaded_by',
    ];
}
