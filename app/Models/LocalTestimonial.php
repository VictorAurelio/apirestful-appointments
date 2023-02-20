<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocalTestimonial extends Model
{
    use HasFactory;

    protected $fillable = [
      'user_id',
      'local_id'
    ];

    protected $table = 'localtestimonials';
    public $timestamps = false;

    public function testimonials() {
      return $this->hasOne(User::class, 'id', 'user_id');
    }
}
