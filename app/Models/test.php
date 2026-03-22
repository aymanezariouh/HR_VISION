<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class test extends Model
{
    /** @use HasFactory<\Database\Factories\TestFactory> */
    use HasFactory;
    protected $fillable =[
        "title" ,"body",
    ];
}
