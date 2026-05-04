<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientUpgrade extends Model {
    use HasFactory;

    protected $table = 'client_upgrades';
    protected $fillable = [
        'client_id', 'user_id', 'comment', 'type'
    ];

    public function client() {
        return $this->belongsTo(Clients::class, 'client_id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
