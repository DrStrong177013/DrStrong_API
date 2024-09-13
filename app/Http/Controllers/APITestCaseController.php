<?php

namespace App\Http\Controllers;

use App\Imports\APITestCasesImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;


class APITestCaseController extends Controller
{
    public function import(Request $request)
    {
        // Upload and process the Excel file
        Excel::import(new APITestCasesImport, $request->file('file'));

        return back()->with('success', 'API test cases imported successfully.');
    }
}
