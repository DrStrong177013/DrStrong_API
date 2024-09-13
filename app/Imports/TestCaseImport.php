<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class TestCaseImport implements ToArray
{
    public function array(array $array)
    {
        // This method will be called when data is imported
        return $array;
    }
}