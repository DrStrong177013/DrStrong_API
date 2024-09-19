<?php

namespace App\Http\Controllers;

use App\Imports\TestCaseImport;
use App\Services\ExcelService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use GuzzleHttp\Client;
use Psy\Readline\Hoa\Console;

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

    //     public function sendTestCases(Request $request)
// {
//     $selectedCases = $request->input('selected_cases');
//     $filePath = $request->input('file_path');

    //     if ($selectedCases && $filePath) {
//         // Đọc tất cả test cases từ file Excel
//         $allTestCases = Excel::toArray(new TestCaseImport, storage_path('app/private/' . $filePath))[0];

    //         // Chuyển đổi chỉ số test case sang số nguyên
//         $selectedCases = array_map('intval', $selectedCases);

    //         // Lọc các test cases đã chọn
//         $selectedTestCases = array_filter($allTestCases, function ($testCase, $index) use ($selectedCases) {
//             return in_array($index, $selectedCases);
//         }, ARRAY_FILTER_USE_BOTH);

    //         // Đặt thứ tự các header mong muốn
//         $completeHeaders = [
//             'Refs',
//             'Testcase',
//             'EndPoint',
//             'Method',
//             'Token',
//             'Body',
//             'StatusCode',
//             'ExpectedResult',
//             'Actual Response',
//             'Result'
//         ];

    //         // Tạo một mảng chỉ số cho từng header trong completeHeaders dựa trên thứ tự của allTestCases[0]
//         $headerIndices = array_flip($allTestCases[0]);

    //         // Đặt thứ tự các cột trong test cases theo completeHeaders và thêm so sánh ExpectedResult với Actual Response
//         $orderedTestCases = array_map(function ($testCase) use ($completeHeaders, $headerIndices) {
//             $orderedRow = [];
//             foreach ($completeHeaders as $header) {
//                 $index = $headerIndices[$header] ?? null;
//                 $orderedRow[] = $index !== null && isset($testCase[$index]) ? $testCase[$index] : 'Failed';
//             }

    //             // Chuyển dữ liệu vào mảng theo thứ tự headers mong muốn
//             $orderedRowAssoc = array_combine($completeHeaders, $orderedRow);

    //             // Kiểm tra và so sánh ExpectedResult với Actual Response
//             if (isset($orderedRowAssoc['ExpectedResult']) && isset($orderedRowAssoc['Actual Response'])) {
//                 $expectedResult = json_decode($orderedRowAssoc['ExpectedResult'], true);
//                 $actualResponse = json_decode($orderedRowAssoc['Actual Response'], true);

    //                 if (json_last_error() === JSON_ERROR_NONE && $expectedResult !== null && $actualResponse !== null) {
//                     $orderedRowAssoc['Result'] = ($expectedResult == $actualResponse) ? 'Passed' : 'Failed';
//                 } else {
//                     // Nếu có lỗi JSON hoặc không có dữ liệu, đánh dấu là "Failed"
//                     $orderedRowAssoc['Result'] = 'Failed';
//                 }
//             } else {
//                 // Nếu thiếu ExpectedResult hoặc Actual Response, đánh dấu là "Failed"
//                 $orderedRowAssoc['Result'] = 'Failed';
//             }

    //             return $orderedRowAssoc;
//         }, $selectedTestCases);

    //         // Trả về trang kết quả với danh sách kết quả
//         return view('test-cases.results', [
//             'results' => $orderedTestCases,
//             'headers' => $completeHeaders
//         ]);
//     } else {
//         // Xử lý khi thiếu test cases hoặc file path
//         if (!$selectedCases) {
//             return back()->with('error', 'Không có test case nào được chọn.');
//         }
//         if (!$filePath) {
//             return back()->with('error', 'Không tìm thấy file.');
//         }
//     }

public function sendTestCases(Request $request)
{
    $selectedCases = $request->input('selected_cases');
    $filePath = $request->input('file_path');

    if (!$selectedCases || !$filePath) {
        return back()->with('error', !$selectedCases ? 'Không có test case nào được chọn.' : 'Không tìm thấy file.');
    }

    // Đọc tất cả test cases từ file Excel
    $allTestCases = Excel::toArray(new TestCaseImport, storage_path('app/private/' . $filePath))[0];

    // Bỏ qua hàng đầu tiên (header)
    $testCasesData = array_slice($allTestCases, 1);

    // Đảm bảo rằng chỉ số của test case là số nguyên
    $selectedCases = array_map('intval', $selectedCases);

    // Các header mà bạn muốn có trong kết quả cuối cùng
    $completeHeaders = [
        'Refs', 'Testcase', 'EndPoint', 'Method', 'Token', 'Body',
        'StatusCode', 'ExpectedResult', 'ActualStatusCode', 'Actual Response', 'Result'
    ];

    $headerIndices = array_flip($allTestCases[0]); // Sử dụng hàng đầu tiên làm header

    // Hàm so sánh JSON với ký tự đại diện
    $compareJsonWithWildcards = function($expected, $actual) {
        $expected = str_replace("'", '"', $expected);
        $actual = str_replace("'", '"', $actual);

        if (trim($expected) === '[...]') {
            $actualArray = json_decode($actual, true);
            return is_array($actualArray) && !empty($actualArray);
        }

        $expectedArray = json_decode($expected, true);
        $actualArray = json_decode($actual, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('JSON Decode Error: ' . json_last_error_msg());
            return false;
        }

        if (is_array($expectedArray) && is_array($actualArray)) {
            foreach ($expectedArray as $key => $value) {
                if ($value === '...') {
                    continue;
                }
                if (!array_key_exists($key, $actualArray) || !$this->deepCompareArrays($value, $actualArray[$key])) {
                    return false;
                }
            }
            return true;
        }

        return trim($expected) === trim($actual);
    };

    // Xử lý tất cả test case và chỉ so sánh với test case được chọn
    $orderedTestCases = array_map(function($testCase, $index) use ($completeHeaders, $headerIndices, $selectedCases, $compareJsonWithWildcards) {
        $orderedRow = [];
        foreach ($completeHeaders as $header) {
            $indexHeader = $headerIndices[$header] ?? null;
            $orderedRow[] = $indexHeader !== null && isset($testCase[$indexHeader]) ? $testCase[$indexHeader] : 'Failed';
        }

        $orderedRowAssoc = array_combine($completeHeaders, $orderedRow);

        // Kiểm tra xem test case này có được chọn hay không
        if (in_array($index, $selectedCases)) {
            try {
                Log::info('Sending request', ['endpoint' => $orderedRowAssoc['EndPoint']]);
                $response = Http::withToken($orderedRowAssoc['Token'])
                    ->{$orderedRowAssoc['Method']}(
                        $orderedRowAssoc['EndPoint'],
                        json_decode($orderedRowAssoc['Body'], true)
                    );

                $orderedRowAssoc['ActualStatusCode'] = $response->status();
                $orderedRowAssoc['Actual Response'] = $response->body();
            } catch (\Exception $e) {
                Log::error('Request error: ' . $e->getMessage());
                $orderedRowAssoc['ActualStatusCode'] = 500;
                $orderedRowAssoc['Actual Response'] = 'Error: ' . $e->getMessage();
            }

            // So sánh Status Code và Response cho các test case được chọn
            if ($orderedRowAssoc['ActualStatusCode'] !== (int)$orderedRowAssoc['StatusCode']) {
                $orderedRowAssoc['Result'] = 'Failed';
            } else if ($compareJsonWithWildcards($orderedRowAssoc['ExpectedResult'], $orderedRowAssoc['Actual Response'])) {
                $orderedRowAssoc['Result'] = 'Passed';
            } else {
                $orderedRowAssoc['Result'] = 'Failed';
            }
        } else {
            // Đối với test case không được chọn, bỏ qua so sánh và đánh dấu kết quả là 'Untested'
            $orderedRowAssoc['ActualStatusCode'] = 'N/A';
            $orderedRowAssoc['Actual Response'] = '';
            $orderedRowAssoc['Result'] = 'Untested';
        }

        return $orderedRowAssoc;
    }, $testCasesData, array_keys($testCasesData));

    // Trả về tất cả test case (bao gồm cả những cái không được chọn)
    return view('test-cases.results', ['results' => $orderedTestCases, 'headers' => $completeHeaders]);
}




// Thêm hàm deepCompareArrays như một phương thức của lớp
private function deepCompareArrays($expected, $actual)
{
    // Kiểm tra xem $expected và $actual có phải là mảng không
    if (is_array($expected) && is_array($actual)) {
        // Nếu $expected là mảng với một phần tử và phần tử đó là '...', trả về true nếu $actual là mảng
        if (count($expected) === 1 && isset($expected[0]) && $expected[0] === '...') {
            return is_array($actual);
        }

        // So sánh các phần tử của hai mảng
        foreach ($expected as $key => $value) {
            if (!array_key_exists($key, $actual) || !$this->deepCompareArrays($value, $actual[$key])) {
                return false;
            }
        }
        return true;
    }

    // So sánh giá trị của $expected và $actual
    return $expected === $actual;
}





}








