<?php

namespace App\Services;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\TestCaseImport;

class ExcelService
{
    public function importTestCases($filePath)
    {
        // Use the Excel facade to import the Excel file
        $data = Excel::toArray(new TestCaseImport, $filePath);

        // Return the imported data
        return $data;
    }
}
