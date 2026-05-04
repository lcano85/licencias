<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class LicenseComment extends Model {
    use HasFactory;
    protected $table = 'license_comments';

    protected $fillable = [
        'license_id', 
        'user_id',
        'lic_comment',
    ];

    public function creator() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
