<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Environment extends Model
{
    protected $table = 'environments';
    protected $fillable = ['name'];
    public $timestamps = true;
}
