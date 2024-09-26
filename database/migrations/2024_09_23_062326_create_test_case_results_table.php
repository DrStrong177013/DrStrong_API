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
            $table->string('Refs')->nullable();
            $table->string('Result')->nullable();
            $table->string('Method')->nullable();
            $table->text('Testcase')->nullable();
            $table->integer('ActualStatusCode')->nullable();
            $table->timestamps();
        });
    }


    public function down()
    {
        Schema::dropIfExists('test_case_results');
    }
}

