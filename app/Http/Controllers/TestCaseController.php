<?php

namespace App\Http\Controllers;

use App\Imports\TestCaseImport;
use App\Services\ExcelService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessTestCases;
use App\Models\TestCaseResult;

class TestCaseController extends Controller
{
    protected $excelService;

    public function __construct(ExcelService $excelService)
    {
        $this->excelService = $excelService;
    }

    public function uploadTestCases(Request $request)
    {
        Log::info('Uploading test cases');
        $file = $request->file('excel_file');

        // Log thông tin về file được tải lên
        Log::info('File uploaded: ', ['file_name' => $file->getClientOriginalName(), 'size' => $file->getSize()]);

        $path = $file->storeAs('uploads', $file->getClientOriginalName());

        // Log đường dẫn file được lưu trữ
        Log::info('File stored at: ', ['path' => $path]);

        $testCases = Excel::toArray(new TestCaseImport, storage_path('app/private/' . $path));
        $allHeaders = $testCases[0][0];
        $desiredHeaders = ["Refs", "Method", "Testcase"];

        // Log thông tin headers của file Excel
        Log::info('Headers found in Excel: ', ['headers' => $allHeaders]);

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
        Log::info('### Starting FUNC sendTestCases ###');

        $selectedCases = $request->input('selected_cases');
        $filePath = $request->input('file_path');

        // Log thông tin dữ liệu gửi lên từ form
        Log::info('Received request data: ', ['selected_cases' => $selectedCases, 'file_path' => $filePath]);

        if (!$selectedCases || !$filePath) {
            return back()->with('error', !$selectedCases ? 'Không có test case nào được chọn.' : 'Không tìm thấy file.');
        }

        try {
            Log::info('Truncating test_case_results table');
            TestCaseResult::truncate();
            Log::info('Table truncated successfully');
            
            // Dispatch job ProcessTestCases để xử lý trong background
            ProcessTestCases::dispatch($filePath, $selectedCases);

            // Log thông tin về job được dispatch
            Log::info('Dispatching job for test cases', ['selected_cases' => $selectedCases, 'file_path' => $filePath]);
            Log::info('$$$ END FUNC sendTestCases ###');

            return redirect()->route('testcases.results');
        } catch (\Exception $e) {
            Log::error('Error dispatching job: ' . $e->getMessage());
            return back()->with('error', 'Error dispatching job.');
        }
    }

    public function getResults()
    {
        Log::info('-----> Getting result');
        $results = TestCaseResult::all();

        if ($results->isEmpty()) {
            Log::info('No results found in database.');
        } else {
            Log::info('Results retrieved from database: ', ['results' => $results]);
        }
        Log::info('-----> END result');

        return view('test-cases.results', compact('results'));
    }

}