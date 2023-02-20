<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocalAvaiability extends Model
{
    use HasFactory;

    protected $table = 'localavaiability';
    public $timestamps = false;
}
