<?php

namespace App\Http\Controllers;

use App\Imports\TestCaseImport;
use App\Services\ExcelService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class TestCaseController extends Controller
{
    protected $excelService;

    public function __construct(ExcelService $excelService)
    {
        $this->excelService = $excelService;
    }
    // public function uploadTestCases(Request $request)
    // {
    //     // Lấy tệp từ request và lưu trữ
    //     $file = $request->file('excel_file');
    //     $path = $file->storeAs('uploads', $file->getClientOriginalName());

    //     // Import dữ liệu từ file Excel
    //     $testCases = Excel::toArray(new TestCaseImport, storage_path('app/private/' . $path));

    //     // Pass dữ liệu test cases và đường dẫn file tới view
    //     return view('test-cases.index', [
    //         'testCases' => array_slice($testCases[0], 1),
    //         'headers' => $testCases[0][0],
    //         'filePath' => $path, // Truyền đường dẫn file tới view
    //     ]);
    // }
    public function uploadTestCases(Request $request)
    {
        $file = $request->file('excel_file');
        $path = $file->storeAs('uploads', $file->getClientOriginalName());

        // Đọc toàn bộ dữ liệu từ Excel
        $testCases = Excel::toArray(new TestCaseImport, storage_path('app/private/' . $path));

        // Lấy tất cả các headers
        $allHeaders = $testCases[0][0]; // Dòng đầu tiên là headers

        // Các cột mong muốn hiển thị ban đầu
        $desiredHeaders = ["Refs", "Testcase", "Method"];

        // Lấy chỉ số các cột mong muốn
        $desiredIndices = array_keys(array_intersect($allHeaders, $desiredHeaders));

        // Lọc test cases để chỉ lấy các cột mong muốn
        $filteredTestCases = array_map(function ($row) use ($desiredIndices) {
            $filteredRow = array_intersect_key($row, array_flip($desiredIndices));
            return array_values($filteredRow);
        }, array_slice($testCases[0], 1)); // Bỏ qua dòng đầu tiên (tiêu đề)

        // Lọc các test cases cho các cột còn lại
        $remainingHeaders = array_diff($allHeaders, $desiredHeaders);
        $remainingIndices = array_keys(array_intersect($allHeaders, $remainingHeaders));

        $remainingTestCases = array_map(function ($row) use ($remainingIndices) {
            $filteredRow = array_intersect_key($row, array_flip($remainingIndices));
            return array_values($filteredRow);
        }, array_slice($testCases[0], 1)); // Bỏ qua dòng đầu tiên (tiêu đề)

        // Đảm bảo remainingHeaders là mảng thay vì đối tượng
        $remainingHeaders = array_values($remainingHeaders);

        // Cập nhật biến headers để truyền vào view
        $headers = $desiredHeaders;

        return view('test-cases.index', [
            'headers' => $headers,
            'testCases' => $filteredTestCases,
            'remainingTestCases' => $remainingTestCases,
            'remainingHeaders' => $remainingHeaders,
            'filePath' => $path,
        ]);
    }

    // Process selected test cases
    public function processSelectedTestCases(Request $request)
    {
        // Lấy các chỉ số test case đã chọn, headers và đường dẫn file từ request
        $selectedIndexes = $request->input('selected_cases');
        $headers = $request->input('headers');
        $filePath = $request->input('file_path');

        if ($selectedIndexes && $filePath) {
            // Đọc toàn bộ test cases từ file Excel
            $allTestCases = Excel::toArray(new TestCaseImport, storage_path('app/private/' . $filePath))[0];

            // Thêm 1 vào chỉ số của test case để xử lý đúng, đảm bảo giá trị là số nguyên
            $selectedIndexes = array_map(function ($index) {
                return intval($index) + 1;
            }, $selectedIndexes);

            // Lọc các test cases đã được chọn dựa vào chỉ số (index) đã điều chỉnh
            $selectedTestCases = array_filter($allTestCases, function ($testCase, $index) use ($selectedIndexes) {
                // Cộng thêm 1 cho chỉ số trong so sánh để phù hợp với chỉ số điều chỉnh
                return in_array(intval($index) + 1, $selectedIndexes);
            }, ARRAY_FILTER_USE_BOTH);

            // Truyền test cases đã được chọn và headers vào view
            return view('test-cases.selected', [
                'headers' => $headers,
                'testCases' => $selectedTestCases,
            ]);
        }

        // Nếu không có test case nào được chọn hoặc không tìm thấy file, quay lại với thông báo lỗi
        return back()->with('error', 'Không có test case nào được chọn hoặc không tìm thấy file.');
    }

}
