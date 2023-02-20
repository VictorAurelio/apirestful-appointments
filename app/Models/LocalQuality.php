<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocalQuality extends Model
{
    use HasFactory;
    
    protected $table = 'localquality';
    public $timestamps = false;
}
