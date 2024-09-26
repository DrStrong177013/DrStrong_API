<?php

namespace App\Http\Controllers;

use App\Imports\TestCaseImport;
use App\Services\ExcelService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessTestCases;


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
        Log::info('### Starting FUNC sendTestCases ###');

        $selectedCases = $request->input('selected_cases');
        $filePath = $request->input('file_path');


        if (!$selectedCases || !$filePath) {
            return back()->with('error', !$selectedCases ? 'Không có test case nào được chọn.' : 'Không tìm thấy file.');
        }

        try {
            // Dispatch job ProcessTestCases để xử lý trong background
            ProcessTestCases::dispatch($filePath, $selectedCases);


            // Gửi thông báo rằng quá trình đang được xử lý trong background
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
        $results = \App\Models\TestCaseResult::all();

        if ($results->isEmpty()) {
            Log::info('No results found in database.');
        } else {
            Log::info('Results retrieved from database: ', ['results' => $results]);
        }
        Log::info('-----> END result');

        return view('test-cases.results', compact('results'));
    }


    public function testBroadcast()
    {
        $data = ['Testcase' => 'Test 1', 'Result' => 'Passed'];

        return response()->json(['status' => 'Event broadcasted']);
    }





}
