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
        Log::info('### Starting job for test cases processing ###');

        try {

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
                'ActualResponse',
                'Result'
            ];

            $headerIndices = array_flip($allTestCases[0]);

            $results = array_map(function ($testCase, $index) use ($completeHeaders, $headerIndices) {
                return $this->processTestCase($testCase, $index, $completeHeaders, $headerIndices, $this->selectedCases);
            }, $testCasesData, array_keys($testCasesData));

            Log::info('Count after processing: ' . TestCaseResult::count());
        } catch (\Exception $e) {
            Log::error('Error processing test cases: ' . $e->getMessage());
        }
    }

    private function processTestCase($testCase, $index, $completeHeaders, $headerIndices, $selectedCases)
    {
        Log::info('Processing test case: ', ['index' => $index]);

        $orderedRow = [];
        foreach ($completeHeaders as $header) {
            $indexHeader = $headerIndices[$header] ?? null;
            $orderedRow[] = $indexHeader !== null && isset($testCase[$indexHeader]) ? $testCase[$indexHeader] : 'Failed';
        }

        $orderedRowAssoc = array_combine($completeHeaders, $orderedRow);
        $adjustedIndex = $index + 1;

        if (empty($selectedCases) || !in_array($adjustedIndex, $selectedCases)) {
            $orderedRowAssoc['ActualStatusCode'] = 'N/A';
            $orderedRowAssoc['ActualResponse'] = '';
            $orderedRowAssoc['Result'] = 'Untested';
            Log::info('No selected cases or index not found', ['index' => $adjustedIndex]);
        } else {
            try {
                Log::info('Sending request', ['endpoint' => $orderedRowAssoc['EndPoint']]);

                $response = Http::timeout(30)
                            ->withToken($orderedRowAssoc['Token'])
                    ->{$orderedRowAssoc['Method']}(
                        $orderedRowAssoc['EndPoint'],
                        json_decode($orderedRowAssoc['Body'], true)
                    );

                $orderedRowAssoc['ActualStatusCode'] = $response->status();
                $orderedRowAssoc['ActualResponse'] = $response->body();
            } catch (ConnectException $e) {
                Log::error('Connection error: ' . $e->getMessage());
                $orderedRowAssoc['ActualStatusCode'] = 408;
                $orderedRowAssoc['ActualResponse'] = 'Connection Error: ' . $e->getMessage();
            } catch (RequestException $e) {
                Log::error('HTTP error: ' . $e->getMessage());
                $orderedRowAssoc['ActualStatusCode'] = $e->getResponse() ? $e->getResponse()->getStatusCode() : 400;
                $orderedRowAssoc['ActualResponse'] = 'Request Error: ' . $e->getMessage();
            } catch (\Exception $e) {
                Log::error('General error: ' . $e->getMessage());
                $orderedRowAssoc['ActualStatusCode'] = 500;
                $orderedRowAssoc['ActualResponse'] = 'Unknown Error: ' . $e->getMessage();
            }

            $orderedRowAssoc['Result'] = ($orderedRowAssoc['ActualStatusCode'] == $orderedRowAssoc['StatusCode']) ? 'Passed' : 'Failed';
        }

        Log::info('Saving test case result', ['Refs' => $orderedRowAssoc['Refs'], 'Result' => $orderedRowAssoc['Result']]);
        $testCaseResult = TestCaseResult::create($orderedRowAssoc);

        Log::info('Preparing to dispatch TestCaseUpdated event', [
            'testCaseResult' => $testCaseResult['Refs'],
        ]);

    }

    private function compareResults($orderedRowAssoc)
    {
        if ($orderedRowAssoc['ActualStatusCode'] !== (int) $orderedRowAssoc['StatusCode']) {
            return 'Failed';
        }

        return $this->compareJsonWithWildcards($orderedRowAssoc['ExpectedResult'], $orderedRowAssoc['ActualResponse']) ? 'Passed' : 'Failed';
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