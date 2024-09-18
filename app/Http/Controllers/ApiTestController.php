<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiTestController extends Controller
{  
//     public function sendTestCases(Request $request)
// {
//     // Lấy các test case được chọn từ form
//     $selectedCases = $request->input('selectedCases', []);

//     if (empty($selectedCases)) {
//         return redirect()->back()->with('error', 'No test cases selected.');
//     }

//     // Tạo dữ liệu test case (hoặc lấy từ cơ sở dữ liệu)
//     $allTestCases = [
//         // Dữ liệu mẫu test case
//     ];

//     // Lọc các test case đã chọn
//     $testCases = array_filter($allTestCases, function ($testCase) use ($selectedCases) {
//         return in_array($testCase['Refs'], $selectedCases);
//     });

//     $results = [];
    
//     foreach ($testCases as $testCase) {
//         // Tạo yêu cầu HTTP dựa trên phương thức
//         $response = Http::withHeaders([
//             'Authorization' => $testCase['Token']
//         ])->{$testCase['Method']}(
//             $testCase['EndPoint'], 
//             $testCase['Body'] ?? []
//         );

//         // Xử lý kết quả thực tế
//         $actualResult = $response->body();
//         $actualStatusCode = $response->status();

//         // So sánh kết quả thực tế với kết quả kỳ vọng
//         $resultStatus = 'Failed';
//         if ($actualStatusCode == $testCase['StatusCode']) {
//             if ($this->compareResults($actualResult, $testCase['ExpectedResult'])) {
//                 $resultStatus = 'Passed';
//             }
//         }

//         $results[] = [
//             'Testcase' => $testCase['Testcase'],
//             'Method' => strtoupper($testCase['Method']),
//             'EndPoint' => $testCase['EndPoint'],
//             'ActualStatusCode' => $actualStatusCode,
//             'Result' => $resultStatus,
//             'ExpectedResult' => $testCase['ExpectedResult'],
//             'ActualResult' => $actualResult,
//         ];
//     }

//     return view('test-cases.results', ['results' => $results]);
// }


}

// public function sendTestCases(Request $request)
    // {
    //     $testCases = [
    //         [
    //             "Refs" => "TC001",
    //             "Testcase" => "Verify GET request to fetch posts",
    //             "EndPoint" => "https://jsonplaceholder.typicode.com/posts",
    //             "Method" => "get",
    //             "Token" => "",
    //             "Body" => null,
    //             "StatusCode" => 200,
    //             "ExpectedResult" => "array", // Ví dụ với kiểu dữ liệu
    //         ],
    //         [
    //             "Refs" => "TC002",
    //             "Testcase" => "Verify POST request to create a new post",
    //             "EndPoint" => "https://jsonplaceholder.typicode.com/posts",
    //             "Method" => "post",
    //             "Token" => "",
    //             "Body" => [
    //                 "title" => "foo",
    //                 "body" => "bar",
    //                 "userId" => 1,
    //             ],
    //             "StatusCode" => 201,
    //             "ExpectedResult" => [
    //                 "title" => "foo",
    //                 "body" => "bar",
    //                 "userId" => 1,
    //             ],
    //         ],
    //         [
    //             "Refs" => "TC003",
    //             "Testcase" => "Verify DELETE request to delete a post",
    //             "EndPoint" => "https://jsonplaceholder.typicode.com/posts/1",
    //             "Method" => "delete",
    //             "Token" => "",
    //             "Body" => null,
    //             "StatusCode" => 200,
    //             "ExpectedResult" => "{}",
    //         ],
    //     ];

    //     $results = [];
    //     foreach ($testCases as $testCase) {
    //         // Tạo yêu cầu HTTP dựa trên phương thức
    //         $response = Http::withHeaders([
    //             'Authorization' => $testCase['Token']
    //         ])->{$testCase['Method']}(
    //             $testCase['EndPoint'], 
    //             $testCase['Body'] ?? []
    //         );

    //         // Xử lý kết quả thực tế
    //         $actualResult = $response->body();
    //         $actualStatusCode = $response->status();

    //         // So sánh kết quả thực tế với kết quả kỳ vọng
    //         $resultStatus = 'Failed';
    //         if ($actualStatusCode == $testCase['StatusCode']) {
    //             if ($this->compareResults($actualResult, $testCase['ExpectedResult'])) {
    //                 $resultStatus = 'Passed';
    //             }
    //         }

    //         $results[] = [
    //             'Testcase' => $testCase['Testcase'],
    //             'Method' => strtoupper($testCase['Method']),
    //             'EndPoint' => $testCase['EndPoint'],
    //             'ActualStatusCode' => $actualStatusCode,
    //             'Result' => $resultStatus,
    //             'ExpectedResult' =>$testCase['ExpectedResult'],
    //             'ActualResult' => $actualResult,
                
    //         ];
    //     }

    //     return view('test-cases.results', ['results' => $results]);
    // }

    // /**
    //  * So sánh kết quả thực tế với kết quả kỳ vọng
    //  *
    //  * @param string $actualResult
    //  * @param mixed $expectedResult
    //  * @return bool
    //  */
    // private function compareResults($actualResult, $expectedResult)
    // {
    //     // Chuyển đổi kết quả thực tế và kỳ vọng thành mảng để so sánh
    //     $actualDecoded = json_decode($actualResult, true);
    //     $expectedDecoded = is_array($expectedResult) ? $expectedResult : json_decode($expectedResult, true);

    //     // So sánh kiểu dữ liệu
    //     if (is_string($expectedResult) && $expectedResult === 'array') {
    //         return is_array($actualDecoded);
    //     }

    //     // So sánh mảng
    //     if (is_array($expectedDecoded)) {
    //         return $actualDecoded === $expectedDecoded;
    //     }

    //     // So sánh chuỗi
    //     return $actualResult === $expectedResult;
    // }