<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestCaseResultsTable extends Migration
{
    public function up()
    {
        Schema::create('test_case_results', function (Blueprint $table) {
            $table->id();
            $table->string('Refs')->nullable();               // Mã tham chiếu
            $table->string('Testcase')->nullable();           // Test case name
            $table->string('EndPoint')->nullable();           // API Endpoint
            $table->string('Method')->nullable();             // Phương thức HTTP
            $table->string('Token')->nullable();              // Token API
            $table->text('Body')->nullable();                 // Body của request
            $table->integer('StatusCode')->nullable();        // Mã trạng thái trả về
            $table->text('ExpectedResult')->nullable();       // Kết quả mong đợi
            $table->integer('ActualStatusCode')->nullable();  // Mã trạng thái thực tế
            $table->text('ActualResponse')->nullable();       // Kết quả thực tế
            $table->string('Result')->nullable();             // Pass/Fail
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('test_case_results');
    }
}