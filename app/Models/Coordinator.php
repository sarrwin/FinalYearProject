<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coordinator extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'department',
        'staff_id',
        // Add other fields you want to be mass assignable
    ];
}
