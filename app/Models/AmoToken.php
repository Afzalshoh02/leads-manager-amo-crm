<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmoToken extends Model
{
    use HasFactory;

    protected $fillable = ['access_token', 'refresh_token', 'expires_at'];
    protected $casts = ['expires_at' => 'datetime'];
}
