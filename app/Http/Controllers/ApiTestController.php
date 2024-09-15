<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class ApiTestController extends Controller
{
    public function runApiTestCases()
    {
        $testCases = [
            [
                "Refs" => "TC001",
                "Testcase" => "Verify GET request to fetch posts",
                "EndPoint" => "https://jsonplaceholder.typicode.com/posts",
                "Method" => "GET",
                "Token" => "", // Không cần token
                "Body" => "", // GET không cần body
                "StatusCode" => 200, // Kỳ vọng 200 (OK)
                "ExpectedResult" => "array", // Kỳ vọng một mảng bất kỳ, không so sánh nội dung cụ thể
            ],

            [
                "Refs" => "TC002",
                "Testcase" => "Verify POST request to create a new post",
                "EndPoint" => "https://jsonplaceholder.typicode.com/posts",
                "Method" => "POST",
                "Token" => "", // Không cần token
                "Body" => json_encode([
                    "title" => "foo",
                    "body" => "bar",
                    "userId" => 1,
                ]),
                "StatusCode" => 201, // Kỳ vọng 201 (Created)
                "ExpectedResult" => json_encode([
                    "id" => 101,
                    "title" => "foo",
                    "body" => "bar",
                    "userId" => 1,
                ]),
            ],
            [
                "Refs" => "TC003",
                "Testcase" => "Verify DELETE request to delete a post",
                "EndPoint" => "https://jsonplaceholder.typicode.com/posts/1",
                "Method" => "DELETE",
                "Token" => "", // Không cần token
                "Body" => "", // DELETE không cần body
                "StatusCode" => 200, // Kỳ vọng 200 (OK)
                "ExpectedResult" => "{}", // Trả về JSON object rỗng
            ],

        ];

        $client = new Client();
        $results = [];

        foreach ($testCases as $testCase) {
            try {
                // Thiết lập headers
                $headers = [
                    'Authorization' => !empty($testCase['Token']) ? 'Bearer ' . $testCase['Token'] : '',
                    'Content-Type' => 'application/json',
                ];

                // Chuẩn bị body nếu có
                $body = !empty($testCase['Body']) ? json_decode($testCase['Body'], true) : [];

                // Gửi request API
                $response = $client->request($testCase['Method'], $testCase['EndPoint'], [
                    'headers' => $headers,
                    'json' => $body,
                ]);

                // Lấy status code và response thực tế
                $statusCode = $response->getStatusCode();
                $actualResponse = json_decode((string) $response->getBody(), true);

                // Lấy expected response (dự kiến) để kiểm tra
                $expectedResponse = json_decode($testCase['ExpectedResult'], true);

                // So sánh linh hoạt
                $isPassed = $this->compareResponses($actualResponse, $expectedResponse);

                // Lưu kết quả chi tiết
                $results[] = [
                    'Testcase' => $testCase['Refs'],
                    'Result' => $isPassed ? 'Passed' : 'Failed',
                    'ExpectedStatusCode' => $testCase['StatusCode'],
                    'ActualStatusCode' => $statusCode,
                    'ExpectedResponse' => $testCase['ExpectedResult'],
                    'ActualResponse' => json_encode($actualResponse, JSON_PRETTY_PRINT),
                ];
            } catch (\Exception $e) {
                // Trường hợp gặp lỗi
                $results[] = [
                    'Testcase' => $testCase['Refs'],
                    'Result' => 'Failed',
                    'Error' => $e->getMessage(),
                    'ExpectedStatusCode' => $testCase['StatusCode'],
                    'ActualStatusCode' => $e->getCode(),
                    'ActualResponse' => '', // Không có response nếu bị lỗi
                ];
            }
        }

        // Trả về view với kết quả chi tiết
        return view('test-cases.results', compact('results'));
    }

/**
 * Hàm so sánh phản hồi linh hoạt giữa actual response và expected response
 */
    private function compareResponses($actualResponse, $expectedResponse)
    {
   

        // Kiểm tra nếu expectedResult là "array" và phản hồi thực tế là một mảng
        if ($expectedResponse === 'array' && is_array($actualResponse)) {
            \Log::info('Response is an array, test passed');
            return true;
        }

        // Nếu cả hai là rỗng hoặc empty (dù là mảng hoặc object), coi như pass
        if ((empty($actualResponse) && empty($expectedResponse)) ||
            (is_array($actualResponse) && empty($actualResponse) && is_array($expectedResponse) && empty($expectedResponse))) {
            return true;
        }

        // Nếu chỉ có một trong hai là empty, coi như fail
        if ((empty($actualResponse) && !empty($expectedResponse)) || (!empty($actualResponse) && empty($expectedResponse))) {
            return false;
        }

        // So sánh linh hoạt giữa object rỗng và mảng rỗng
        if ((is_array($actualResponse) && empty($actualResponse) && $expectedResponse == (object) []) ||
            ($actualResponse == (object) [] && is_array($expectedResponse) && empty($expectedResponse))) {
            return true;
        }

        // So sánh từng phần tử nếu là array/object
        if (is_array($actualResponse) && is_array($expectedResponse)) {
            return $this->checkArrayForExpectedFields($actualResponse, $expectedResponse);
        }

        // So sánh nguyên bản
        return $actualResponse == $expectedResponse;
    }

/**
 * Kiểm tra xem các trường trong expected response có tồn tại trong actual response không
 */
    private function checkArrayForExpectedFields($actualResponse, $expectedFields)
    {
        foreach ($expectedFields as $key => $value) {
            if (!array_key_exists($key, $actualResponse)) {
                return false; // Trường mong đợi không tồn tại
            }

            // Nếu giá trị là mảng hoặc object, kiểm tra đệ quy
            if (is_array($value)) {
                if (!$this->checkArrayForExpectedFields($actualResponse[$key], $value)) {
                    return false; // Nếu một trong các kiểm tra con không thành công
                }
            } elseif ($actualResponse[$key] != $value) {
                return false; // Giá trị không khớp
            }
        }
        return true;
    }

}
//
