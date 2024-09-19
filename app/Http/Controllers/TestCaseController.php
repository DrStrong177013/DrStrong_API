<?php

namespace App\Http\Controllers;

use App\Imports\TestCaseImport;
use App\Services\ExcelService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

    if ($selectedCases && $filePath) {
        // Đọc tất cả test cases từ file Excel
        $allTestCases = Excel::toArray(new TestCaseImport, storage_path('app/private/' . $filePath))[0];

        // Chuyển đổi chỉ số test case sang số nguyên
        $selectedCases = array_map('intval', $selectedCases);

        // Lọc các test cases đã chọn
        $selectedTestCases = array_filter($allTestCases, function ($testCase, $index) use ($selectedCases) {
            return in_array($index, $selectedCases);
        }, ARRAY_FILTER_USE_BOTH);

        // Đặt thứ tự các header mong muốn, thêm ActualStatusCode
        $completeHeaders = [
            'Refs',
            'Testcase',
            'EndPoint',
            'Method',
            'Token',
            'Body',
            'StatusCode',
            'ExpectedResult',
            'ActualStatusCode',
            'Actual Response',
            'Result'
        ];

        // Tạo một mảng chỉ số cho từng header trong completeHeaders dựa trên thứ tự của allTestCases[0]
        $headerIndices = array_flip($allTestCases[0]);

        // Helper function to determine ActualStatusCode in case of errors
        function getFallbackStatusCode(\Exception $e)
        {
            if ($e instanceof \Illuminate\Http\Client\RequestException) {
                return $e->getCode() ?: 500;  // Fallback to 500 if no code available
            }
            return 500;  // Default to server error
        }

        // Function to compare JSON structures ignoring formatting issues like spaces and line breaks
        function compareJsonStructures($expected, $actual)
        {
            // Decode JSON strings into arrays for comparison
            $expectedArray = json_decode($expected, true);
            $actualArray = json_decode($actual, true);

            // Check if both are valid JSON
            if (json_last_error() === JSON_ERROR_NONE) {
                // Deep compare arrays (even nested ones)
                return deepCompareArrays($expectedArray, $actualArray);
            }
            return false;
        }

        function compareJsonWithWildcards($expected, $actual)
        {
            // Decode both expected and actual results
            $expectedArray = json_decode($expected, true);
            $actualArray = json_decode($actual, true);

            // Check if expected result contains "..." (wildcard) for ignoring parts of the result
            if (is_string($expected) && strpos($expected, '...') !== false) {
                // Treat '...' as a wildcard, remove it from comparison
                $normalizedExpected = preg_replace('/\.\.\./', '', $expected);
                return strpos($actual, $normalizedExpected) !== false;
            }

            // Check if both are valid JSON and proceed to comparison
            if (json_last_error() === JSON_ERROR_NONE) {
                // Compare arrays deeply if they are valid JSON
                return deepCompareArrays($expectedArray, $actualArray);
            }

            // If they are not JSON, treat them as strings and do a string comparison
            return trim($expected) === trim($actual);
        }

        // Function to deeply compare arrays, allowing for wildcards
        function deepCompareArrays($expected, $actual)
        {
            if (is_array($expected) && is_array($actual)) {
                // Handle case where expected result is an array with '[...]' wildcard
                if (count($expected) === 1 && $expected[0] === '...') {
                    // Accept any array for '...' wildcard
                    return is_array($actual);
                }

                // Compare each key-value pair in the arrays
                foreach ($expected as $key => $value) {
                    if (!array_key_exists($key, $actual) || !deepCompareArrays($value, $actual[$key])) {
                        return false;
                    }
                }
                return true;
            } elseif (is_string($expected) && is_string($actual)) {
                // Compare strings after trimming spaces
                return trim($expected) === trim($actual);
            } else {
                // Direct comparison for other data types
                return $expected === $actual;
            }
        }

        // Đặt thứ tự các cột trong test cases theo completeHeaders và thêm so sánh ExpectedResult với Actual Response
        $orderedTestCases = array_map(function ($testCase) use ($completeHeaders, $headerIndices) {
            $orderedRow = [];
            foreach ($completeHeaders as $header) {
                $index = $headerIndices[$header] ?? null;
                $orderedRow[] = $index !== null && isset($testCase[$index]) ? $testCase[$index] : 'Failed';
            }

            // Chuyển dữ liệu vào mảng theo thứ tự headers mong muốn
            $orderedRowAssoc = array_combine($completeHeaders, $orderedRow);

            // Thực hiện kiểm tra API
            try {
                // Ghi log trước khi gửi request
                Log::info('Sending request to API', [
                    'url' => $orderedRowAssoc['EndPoint'],
                    'method' => $orderedRowAssoc['Method'],
                    'body' => $orderedRowAssoc['Body']
                ]);

                $response = Http::withToken($orderedRowAssoc['Token'])
                    ->{$orderedRowAssoc['Method']}(
                        $orderedRowAssoc['EndPoint'],
                        json_decode($orderedRowAssoc['Body'], true)
                    );

                // Ghi log khi nhận response
                Log::info('Received response', [
                    'status_code' => $response->status(),
                    'response_body' => $response->body()
                ]);

                // Lưu Actual Response và ActualStatusCode nếu API thành công
                $orderedRowAssoc['ActualStatusCode'] = $response->status();
                $orderedRowAssoc['Actual Response'] = $response->body();
            } catch (\Exception $e) {
                // Ghi log lỗi nếu có exception
                Log::error('API error for ' . $orderedRowAssoc['Refs'] . ': ' . $e->getMessage());

                // Ghi log về lỗi ngoại lệ và trả về status code mặc định
                $orderedRowAssoc['ActualStatusCode'] = getFallbackStatusCode($e);
                $orderedRowAssoc['Actual Response'] = 'Error: ' . $e->getMessage();
            }

            // So sánh ActualStatusCode với StatusCode
            if (isset($orderedRowAssoc['StatusCode']) && isset($orderedRowAssoc['ActualStatusCode'])) {
                if ((string) $orderedRowAssoc['StatusCode'] !== (string) $orderedRowAssoc['ActualStatusCode']) {
                    // Ghi log nếu status code không khớp
                    Log::warning('Mismatched status code', [
                        'expected' => $orderedRowAssoc['StatusCode'],
                        'actual' => $orderedRowAssoc['ActualStatusCode']
                    ]);

                    // Ghi lại khác biệt về status code vào Actual Response
                    $orderedRowAssoc['Actual Response'] .= ' | Expect status ' . $orderedRowAssoc['StatusCode'] . ' but response ' . $orderedRowAssoc['ActualStatusCode'];
                    $orderedRowAssoc['Result'] = 'Failed';
                }
            }

            // So sánh ExpectedResult với Actual Response
            if (isset($orderedRowAssoc['ExpectedResult']) && isset($orderedRowAssoc['Actual Response'])) {
                $expectedResult = $orderedRowAssoc['ExpectedResult'];
                $actualResponse = $orderedRowAssoc['Actual Response'];

                // Ghi log trước khi so sánh JSON
                Log::info('Comparing results', [
                    'expected' => $expectedResult,
                    'actual' => $actualResponse
                ]);

                // So sánh JSON hoặc chuỗi, cho phép dấu '...' trong ExpectedResult
                if (compareJsonWithWildcards($expectedResult, $actualResponse)) {
                    $orderedRowAssoc['Result'] = 'Passed';
                } else {
                    $orderedRowAssoc['Result'] = 'Failed';
                }
            } else {
                // Nếu thiếu ExpectedResult hoặc Actual Response, đánh dấu là "Failed"
                $orderedRowAssoc['Result'] = 'Failed';
            }

            return $orderedRowAssoc;
        }, $selectedTestCases);

        // Trả về trang kết quả với danh sách kết quả
        return view('test-cases.results', [
            'results' => $orderedTestCases,
            'headers' => $completeHeaders
        ]);
    } else {
        // Xử lý khi thiếu test cases hoặc file path
        if (!$selectedCases) {
            return back()->with('error', 'Không có test case nào được chọn.');
        }
        if (!$filePath) {
            return back()->with('error', 'Không tìm thấy file.');
        }
    }
}


}








