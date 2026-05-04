<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ClientCategory;

class ClientSubCategory extends Model {
    use HasFactory;

    protected $table = 'client_subcategory';
    protected $fillable = [
        'categoryID', 'subcategory_name', 'subcategory_status'
    ];

    public function mainCategory() {
        return $this->belongsTo(ClientCategory::class, 'categoryID');
    }
}
