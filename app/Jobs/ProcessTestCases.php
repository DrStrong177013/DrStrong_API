<?php

namespace App\Jobs;

use App\Imports\TestCaseImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use App\Models\TestCaseResult;
use App\Events\TestCaseResultsUpdated;

class ProcessTestCases implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    protected $filePath;
    protected $selectedCases;

    public function __construct($filePath, $selectedCases)
    {
        $this->filePath = $filePath;
        $this->selectedCases = $selectedCases;
    }

    public function handle()
    {
        Log::info('###Starting job for test cases processing');

        try {
            Log::info('Truncating test_case_results table');
            TestCaseResult::truncate();
            Log::info('Table truncated successfully');

            $allTestCases = Excel::toArray(new TestCaseImport, storage_path('app/private/' . $this->filePath))[0];

            $testCasesData = array_slice($allTestCases, 1);

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

            $headerIndices = array_flip($allTestCases[0]);

            $results = array_map(function ($testCase, $index) use ($completeHeaders, $headerIndices) {
                return $this->processTestCase($testCase, $index, $completeHeaders, $headerIndices, $this->selectedCases);
            }, $testCasesData, array_keys($testCasesData));

            session(['test_case_results' => $results]);

            $count = TestCaseResult::count();
            Log::info('Count after processing: ' . $count);

        } catch (\Exception $e) {
            Log::error('Error processing test cases: ' . $e->getMessage());
        }
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

        if (empty($selectedCases) || !in_array($adjustedIndex, $selectedCases)) {
            $orderedRowAssoc['ActualStatusCode'] = 'N/A';
            $orderedRowAssoc['Actual Response'] = '';
            $orderedRowAssoc['Result'] = 'Untested';
            Log::info('No selected cases or index not found', ['index' => $adjustedIndex]);
        } else {
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
                $orderedRowAssoc['ActualStatusCode'] = 408;
                $orderedRowAssoc['Actual Response'] = 'Connection Error: ' . $e->getMessage();
            } catch (RequestException $e) {
                Log::error('HTTP error: ' . $e->getMessage());
                $orderedRowAssoc['ActualStatusCode'] = $e->getResponse() ? $e->getResponse()->getStatusCode() : 400;
                $orderedRowAssoc['Actual Response'] = 'Request Error: ' . $e->getMessage();
            } catch (\Exception $e) {
                Log::error('General error: ' . $e->getMessage());
                $orderedRowAssoc['ActualStatusCode'] = 500;
                $orderedRowAssoc['Actual Response'] = 'General Error: ' . $e->getMessage();
            }

            $orderedRowAssoc['Result'] = $this->compareResults($orderedRowAssoc);
        }
        Log::info('Method:', ['Method' => $orderedRowAssoc['Method']]);
        
        $result = new TestCaseResult();
        $result->Refs = $orderedRowAssoc['Refs'];
        $result->Result = $orderedRowAssoc['Result'];
        $result->Method = $orderedRowAssoc['Method'] ?? 'UNKNOWN';
        $result->Testcase = $orderedRowAssoc['Testcase'] ?? 'No Testcase Provided';
        $result->ActualStatusCode = $orderedRowAssoc['ActualStatusCode'] ?? 0;

        $result->save();

        Log::info('Saved result to database', ['Refs' => $orderedRowAssoc['Refs'], 'Result' => $orderedRowAssoc['Result']]);
        Log::info('Data for broadcast: ', $orderedRowAssoc);

        // Broadcasting the result for real-time updates
        try {
            // Log::info('<<<<<BEGIN broadcasting ' . $orderedRowAssoc['Testcase'] . '>>>>>');
            // broadcast(new TestCaseResultsUpdated($orderedRowAssoc));
            // // event(new TestCaseResultsUpdated($orderedRowAssoc));
            // // event(new TestCaseResultsUpdated(new \Illuminate\Database\Eloquent\Collection()));
            // Log::info('-----END for broacasting' . $orderedRowAssoc['Testcase'] . '-----');
        } catch (\Exception $e) {
            Log::error('!!! Broadcast ERROR: ' . $e->getMessage() . ' !!!');
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
