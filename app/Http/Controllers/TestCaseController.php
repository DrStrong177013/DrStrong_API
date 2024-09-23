<?php

namespace App\Http\Controllers;

use App\Imports\TestCaseImport;
use App\Services\ExcelService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Facades\Storage;

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
    public function sendTestCases(Request $request)
    {
        $selectedCases = $request->input('selected_cases');
        $filePath = $request->input('file_path');
        if (!$selectedCases || !$filePath) {
            return back()->with('error', !$selectedCases ? 'Không có test case nào được chọn.' : 'Không tìm thấy file.');
        }

        try {
            $allTestCases = Excel::toArray(new TestCaseImport, storage_path('app/private/' . $filePath))[0];

            // Bỏ qua hàng đầu tiên (header)
            $testCasesData = array_slice($allTestCases, 1);

            // Các header mà bạn muốn có trong kết quả cuối cùng
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

            $headerIndices = array_flip($allTestCases[0]); // Sử dụng hàng đầu tiên làm header

            $orderedTestCases = array_map(function ($testCase, $index) use ($completeHeaders, $headerIndices, $selectedCases) {
                return $this->processTestCase($testCase, $index, $completeHeaders, $headerIndices, $selectedCases);
            }, $testCasesData, array_keys($testCasesData));

            return view('test-cases.results', ['results' => $orderedTestCases, 'headers' => $completeHeaders]);
        } catch (\Exception $e) {
            Log::error('Error sending test cases: ' . $e->getMessage());
            return back()->with('error', 'Error processing test cases.');
        }
    }

    private function reorderRow($row, $headerIndices)
    {
        return array_map(function ($header) use ($row, $headerIndices) {
            return $row[$headerIndices[$header]] ?? 'N/A';
        }, array_keys($headerIndices));
    }

    private function processTestCase($testCase, $index, $completeHeaders, $headerIndices, $selectedCases)
    {
        $orderedRow = [];
        foreach ($completeHeaders as $header) {
            $indexHeader = $headerIndices[$header] ?? null;
            $orderedRow[] = $indexHeader !== null && isset($testCase[$indexHeader]) ? $testCase[$indexHeader] : 'Failed';
        }

        $orderedRowAssoc = array_combine($completeHeaders, $orderedRow);
        $adjustedIndex = $index + 1;

        if (in_array($adjustedIndex, $selectedCases)) {
            try {
                Log::info('Sending request', ['endpoint' => $orderedRowAssoc['EndPoint']]);
            
                $response = Http::withToken($orderedRowAssoc['Token'])
                    ->{$orderedRowAssoc['Method']}(
                        $orderedRowAssoc['EndPoint'],
                        json_decode($orderedRowAssoc['Body'], true)
                    );
            
                $orderedRowAssoc['ActualStatusCode'] = $response->status();
                $orderedRowAssoc['Actual Response'] = $response->body();
            } catch (ConnectException $e) {
                Log::error('Connection error: ' . $e->getMessage());
                $orderedRowAssoc['ActualStatusCode'] = 408; // Mã 408 (Request Timeout) cho lỗi kết nối
                $orderedRowAssoc['Actual Response'] = 'Connection Error: ' . $e->getMessage();
            } catch (RequestException $e) {
                Log::error('HTTP error: ' . $e->getMessage());
                $orderedRowAssoc['ActualStatusCode'] = $e->getResponse() ? $e->getResponse()->getStatusCode() : 400; // Lấy status code từ phản hồi hoặc mặc định là 400 (Bad Request)
                $orderedRowAssoc['Actual Response'] = 'Request Error: ' . $e->getMessage();
            } catch (\Exception $e) {
                Log::error('General error: ' . $e->getMessage());
                $orderedRowAssoc['ActualStatusCode'] = 500; // 500 cho các lỗi chung khác
                $orderedRowAssoc['Actual Response'] = 'General Error: ' . $e->getMessage();
            }

            $orderedRowAssoc['Result'] = $this->compareResults($orderedRowAssoc);
        } else {
            $orderedRowAssoc['ActualStatusCode'] = 'N/A';
            $orderedRowAssoc['Actual Response'] = '';
            $orderedRowAssoc['Result'] = 'Untested';
        }

        return $orderedRowAssoc;
    }

    private function compareResults($orderedRowAssoc)
    {
        if ($orderedRowAssoc['ActualStatusCode'] !== (int) $orderedRowAssoc['StatusCode']) {
            return 'Failed';
        }

        return $this->compareJsonWithWildcards($orderedRowAssoc['ExpectedResult'], $orderedRowAssoc['Actual Response']) ? 'Passed' : 'Failed';
    }

    private function deepCompareArrays($expected, $actual)
    {
        if (is_array($expected) && is_array($actual)) {
            if (count($expected) === 1 && isset($expected[0]) && $expected[0] === '...') {
                return is_array($actual);
            }

            foreach ($expected as $key => $value) {
                if (!array_key_exists($key, $actual) || !$this->deepCompareArrays($value, $actual[$key])) {
                    return false;
                }
            }
            return true;
        }

        return $expected === $actual;
    }

    private function compareJsonWithWildcards($expected, $actual)
    {
        $expected = str_replace("'", '"', $expected);
        $actual = str_replace("'", '"', $actual);

        if (trim($expected) === '[...]') {
            return is_array(json_decode($actual, true)) && !empty(json_decode($actual, true));
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
    }
}
