<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestCaseController;
use App\Http\Controllers\ApiTestController;

// Route for showing the file upload form
Route::get('/', function () {
    return view('upload'); // Displays the file upload form
});

// Route for uploading and displaying test cases
Route::post('/upload-test-cases', [TestCaseController::class, 'uploadTestCases'])->name('uploadTestCases');

// Route for processing the selected test cases
Route::post('/process-selected-test-cases', [TestCaseController::class, 'processSelectedTestCases'])->name('processTestCases');


// Route::get('/', [ApiTestController::class, 'runApiTestCases']);