<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestCaseController;
use App\Http\Controllers\ApiTestController;
use Illuminate\Support\Facades\Log;

// Route for showing the file upload form
Route::get('/', function () {
    return view('upload'); // Displays the file upload form
});

// Route for uploading and displaying test cases
Route::post('/upload-test-cases', [TestCaseController::class, 'uploadTestCases'])->name('uploadTestCases');

// Route for processing the selected test cases
// Route::post('/process-selected-test-cases', [TestCaseController::class, 'processSelectedTestCases'])->name('processTestCases');


// routes/web.php
Route::post('/send-test-cases', [TestCaseController::class, 'sendTestCases'])->name('sendTestCases');

Route::get('/run-ollama-commands', function () {
    // Lệnh đầu tiên: 'ollama list'
    $command1 = 'ollama list';
    $output1 = shell_exec($command1);

    // Kiểm tra đầu ra và chuyển đổi mã hóa bằng iconv
    if ($output1 === null) {
        return response()->json([
            'status' => 'error',
            'message' => 'Lệnh "ollama list" không thành công.',
        ]);
    }

    $output1 = iconv('ISO-8859-1', 'UTF-8//IGNORE', $output1);

    // Lệnh thứ hai: 'ollama run llama3.1'
    $command2 = 'ollama run llama3.1';
    $process = proc_open($command2, [
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ], $pipes);

    if (is_resource($process)) {
        $timeout = 60 * 5;
        $startTime = time();
        $output2 = '';

        while (true) {
            $read = [$pipes[1]];
            $write = null;
            $except = null;

            if (stream_select($read, $write, $except, 1) > 0) {
                $output2 .= fread($pipes[1], 1024);
            }

            if (time() - $startTime > $timeout) {
                proc_terminate($process);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Lệnh "ollama run llama3.1" đã hết thời gian chờ.',
                ]);
            }

            if (feof($pipes[1])) {
                break;
            }
        }

        fclose($pipes[1]);
        fclose($pipes[2]);
        $return_value = proc_close($process);

        // Kiểm tra và chuyển đổi mã hóa
        if ($return_value !== 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lệnh "ollama run llama3.1" không thành công.',
            ]);
        }

        $output2 = iconv('ISO-8859-1', 'UTF-8//IGNORE', $output2);
    } else {
        return response()->json([
            'status' => 'error',
            'message' => 'Không thể chạy lệnh "ollama run llama3.1".',
        ]);
    }

    // Lệnh thứ ba: 'echo "xin chào"'
    $command3 = 'echo "Bạn giới thiệu ngắn về bản thân bạn đi."';
    $output3 = shell_exec($command3);

    if ($output3 === null) {
        return response()->json([
            'status' => 'error',
            'message' => 'Lệnh "xin chào" không thành công.',
        ]);
    }

    $output3 = iconv('ISO-8859-1', 'UTF-8//IGNORE', $output3);

    // Đợi 20 giây
    sleep(20);

    // Lệnh thứ tư: '/bye'
    $command4 = 'echo "/bye"';
    $output4 = shell_exec($command4);

    if ($output4 === null) {
        return response()->json([
            'status' => 'error',
            'message' => 'Lệnh "/bye" không thành công.',
        ]);
    }

    $output4 = iconv('ISO-8859-1', 'UTF-8//IGNORE', $output4);
    Log::info('Output of command1', ['output' => $output1]);
    Log::info('Output of command2', ['output' => $output2]);
    Log::info('Output of command3', ['output' => $output3]);
    Log::info('Output of command4', ['output' => $output4]);

    // Trả về kết quả của tất cả các lệnh
    return response()->json([
        'status' => 'success',
        'output1' => trim($output1),
        'output2' => trim($output2),
        'output3' => trim($output3),
        'output4' => trim($output4),
    ]);
});








