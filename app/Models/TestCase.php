<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestCase extends Model
{
    // Các thuộc tính có thể được gán hàng loạt
    protected $fillable = [
        'ref',               // Mã tham chiếu của test case
        'endpoint',          // URL endpoint API
        'method',            // Phương thức HTTP (GET, POST, PUT, DELETE)
        'token',             // Token API (nếu cần)
        'body',              // Nội dung body của request
        'status_code',       // Mã trạng thái trả về
        'expected_result',   // Kết quả mong đợi
        'actual_response',   // Kết quả thực tế sau khi gửi request
        'result',            // Kết quả kiểm tra (Pass/Fail)
    ];
}
