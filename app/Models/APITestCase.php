<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class APITestCase extends Model
{
    use HasFactory;
    protected $fillable = ['endpoint', 'method', 'input_data', 'expected_output'];
}
