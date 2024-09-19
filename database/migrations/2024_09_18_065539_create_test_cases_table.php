<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestCasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('test_cases', function (Blueprint $table) {
            $table->id();
            $table->string('ref');                 // Mã tham chiếu
            $table->string('endpoint');            // API Endpoint
            $table->string('method');              // Phương thức HTTP
            $table->string('token')->nullable();   // Token API
            $table->text('body')->nullable();      // Body của request
            $table->integer('status_code');        // Mã trạng thái trả về
            $table->text('expected_result');       // Kết quả mong đợi
            $table->text('actual_response')->nullable(); // Kết quả thực tế
            $table->string('result')->nullable();  // Pass/Fail
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('test_cases');
    }
}
