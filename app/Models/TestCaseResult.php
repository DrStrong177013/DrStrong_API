<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Events\TestCaseUpdated;

class TestCaseResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'Refs',               // Mã tham chiếu
        'Testcase',           // Test case name
        'EndPoint',           // API Endpoint
        'Method',             // Phương thức HTTP
        'Token',              // Token API
        'Body',               // Body của request
        'StatusCode',         // Mã trạng thái trả về
        'ExpectedResult',     // Kết quả mong đợi
        'ActualStatusCode',   // Mã trạng thái thực tế
        'ActualResponse',     // Kết quả thực tế
        'Result',             // Pass/Fail
    ];
    protected static function booted()
    {
        // static::created(function ($testCaseResult) {
        //     event(new TestCaseUpdated($testCaseResult));
        // });
    }
}