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
    $selectedIndexes = $request->input('selected_cases');
    $headers = $request->input('headers');
    $remainingHeaders = $request->input('remaining_headers', []); // Lấy remaining_headers từ request
    $filePath = $request->input('file_path');

    if ($selectedIndexes && $filePath) {
        $allTestCases = Excel::toArray(new TestCaseImport, storage_path('app/private/' . $filePath))[0];

        // Cộng thêm 1 vào chỉ số của test case để xử lý đúng
        $selectedIndexes = array_map(function ($index) {
            return intval($index) + 1;
        }, $selectedIndexes);

        // Lọc test cases dựa trên selectedIndexes
        $selectedTestCases = array_filter($allTestCases, function ($testCase, $index) use ($selectedIndexes) {
            return in_array(intval($index), $selectedIndexes);
        }, ARRAY_FILTER_USE_BOTH);

        // Đặt thứ tự các header mong muốn
        $completeHeaders = [
            'Refs', 'Testcase', 'EndPoint', 'Method', 'Token', 'Body', 'StatusCode', 'ExpectedResult', 'Actual Response', 'Result'
        ];

        // Tạo một mảng chỉ số cho từng header trong completeHeaders dựa trên thứ tự của allTestCases[0]
        $headerIndices = array_flip($allTestCases[0]);

        $orderedTestCases = array_map(function ($testCase) use ($completeHeaders, $headerIndices) {
            $orderedRow = [];
            foreach ($completeHeaders as $header) {
                // Lấy dữ liệu từ test case theo chỉ số của header
                $index = $headerIndices[$header] ?? null;
                $orderedRow[] = $index !== null && isset($testCase[$index]) ? $testCase[$index] : 'N/A';
            }
            return $orderedRow;
        }, $selectedTestCases);

        return view('test-cases.selected', [
            'headers' => $completeHeaders, // Truyền headers đầy đủ
            'testCases' => $orderedTestCases,
        ]);
    }

    return back()->with('error', 'Không có test case nào được chọn hoặc không tìm thấy file.');
}


}
