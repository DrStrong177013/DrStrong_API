<?php

namespace App\Services;
use App\Models\TestCase;

<?php

namespace App\Services;

use App\Models\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class TestCaseService
{
    /**
     * Lấy danh sách các test case từ request (ví dụ như từ file Excel)
     */
    public function getTestCases(Request $request): Collection
    {
        // Giả định rằng request đã gửi file Excel và được xử lý trước đó,
        // trả về một collection các test case.
        $filePath = $request->input('file_path');
        $headers = $request->input('headers');
        $selectedCases = $request->input('selected_cases');

        // Xử lý file Excel và chọn các test case cần thiết
        // Bạn có thể dùng package như Maatwebsite Excel để đọc file

        $testCases = collect(); // Collection trống, sẽ điền từ file Excel
        // Đọc và thêm test case vào collection ở đây (giả định)

        return $testCases;
    }

    /**
     * Chạy một test case, gửi API request, và lưu kết quả
     */
    public function runTestCase(TestCase $testCase): TestCase
    {
        // Chuẩn bị API request từ test case
        $response = Http::withToken($testCase->token)
            ->$testCase->method($testCase->endpoint, json_decode($testCase->body, true));

        // Lưu kết quả response thực tế
        $testCase->actual_response = $response->body();
        $testCase->result = ($response->status() == $testCase->status_code && $response->body() == $testCase->expected_result) ? 'Pass' : 'Fail';
        
        // Cập nhật test case vào database
        $testCase->save();

        return $testCase;
    }

    /**
     * Chạy tất cả các test case đã được chọn
     */
    public function runAllTestCases(Collection $testCases): Collection
    {
        foreach ($testCases as $testCase) {
            $this->runTestCase($testCase);
        }

        return $testCases;
    }
}


