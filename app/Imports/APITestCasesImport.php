<?php

namespace App\Imports;

use App\Models\APITestCase;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class APITestCasesImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new APITestCase([
            'endpoint' => $row['endpoint'],
            'method' => $row['method'],
            'input_data' => $row['input_data'],
            'expected_output' => $row['expected_output'],
        ]);
    }
}
