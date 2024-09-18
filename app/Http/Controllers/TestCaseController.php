<?php

namespace App\Http\Controllers;

use App\Imports\TestCaseImport;
use App\Services\ExcelService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Http;

use GuzzleHttp\Client;

class TestCaseController extends Controller
{
    protected $excelService;

    public function __construct(ExcelService $excelService)
    {
        $this->excelService = $excelService;
    }

    public function uploadTestCases(Request $request)
    {
        $file = $request->file('excel_file');
        $path = $file->storeAs('uploads', $file->getClientOriginalName());

        $testCases = Excel::toArray(new TestCaseImport, storage_path('app/private/' . $path));
        $allHeaders = $testCases[0][0];
        $desiredHeaders = ["Refs", "Method", "Testcase"];

        $headerIndices = array_flip($allHeaders);
        $orderedIndices = array_map(function ($header) use ($headerIndices) {
            return $headerIndices[$header] ?? null;
        }, $desiredHeaders);

        $filteredTestCases = array_map(function ($row) use ($orderedIndices) {
            $orderedRow = [];
            foreach ($orderedIndices as $index) {
                $orderedRow[] = $row[$index] ?? 'N/A';
            }
            return $orderedRow;
        }, array_slice($testCases[0], 1));

        $remainingHeaders = array_diff($allHeaders, $desiredHeaders);
        $remainingIndices = array_flip(array_intersect($allHeaders, $remainingHeaders));

        $remainingTestCases = array_map(function ($row) use ($remainingIndices) {
            $orderedRow = [];
            foreach ($remainingIndices as $index) {
                $orderedRow[] = $row[$index] ?? 'N/A';
            }
            return $orderedRow;
        }, array_slice($testCases[0], 1));

        $remainingHeaders = array_values($remainingHeaders);

        return view('test-cases.index', [
            'headers' => $desiredHeaders,
            'testCases' => $filteredTestCases,
            'remainingHeaders' => $remainingHeaders,
            'remainingTestCases' => $remainingTestCases,
            'filePath' => $path,
        ]);
    }

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
    public function sendTestCases(Request $request)
{
    $selectedCases = $request->input('selected_cases');
    $headers = $request->input('headers');
    $filePath = $request->input('file_path');

    if ($selectedCases && $filePath) {
        // Đọc tất cả test cases từ file Excel
        $allTestCases = Excel::toArray(new TestCaseImport, storage_path('app/private/' . $filePath))[0];

        // Thêm 1 vào chỉ số của test case để xử lý đúng
        $selectedCases = array_map('intval', $selectedCases);

        // Lọc test cases đã chọn
        $selectedTestCases = array_filter($allTestCases, function ($testCase, $index) use ($selectedCases) {
            return in_array($index, $selectedCases);
        }, ARRAY_FILTER_USE_BOTH);

        // Đặt thứ tự các header mong muốn
        $completeHeaders = [
            'Refs',
            'Testcase',
            'EndPoint',
            'Method',
            'Token',
            'Body',
            'StatusCode',
            'ExpectedResult',
            'Actual Response',
            'Result'
        ];

        // Tạo một mảng chỉ số cho từng header trong completeHeaders dựa trên thứ tự của allTestCases[0]
        $headerIndices = array_flip($allTestCases[0]);

        // Đặt thứ tự các cột trong test cases theo completeHeaders
        $orderedTestCases = array_map(function ($testCase) use ($completeHeaders, $headerIndices) {
            $orderedRow = [];
            foreach ($completeHeaders as $header) {
                $index = $headerIndices[$header] ?? null;
                $orderedRow[] = $index !== null && isset($testCase[$index]) ? $testCase[$index] : 'N/A';
            }
            return array_combine($completeHeaders, $orderedRow);
        }, $selectedTestCases);

        // Trả về trang kết quả với danh sách kết quả
        return view('test-cases.results', [
            'results' => $orderedTestCases,
            'headers' => $completeHeaders
        ]);
    }

    // Xử lý trường hợp không có test case được chọn hoặc không tìm thấy file
    return back()->with('error', 'Không có test case nào được chọn hoặc không tìm thấy file.');
}



}