<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Log;

class TestCaseTableComponent extends Component
{
    public $results;
    protected $listeners = ['refreshChildComponent' => 'refresh'];

    public function mount($results)
    {
        log::info('mount testcase');
        $this->results = $results;
   
    }
    

    public function refresh($results)
    {
        try {
            $this->results = $results;

            log::info('refresh testcase');
  
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    public function transformResults()
{
    foreach ($this->results as &$result) {
        // Ensure that ExpectedResult and ActualResponse are valid JSON
        if (!is_null($result['ExpectedResult'])) {
            $result['ExpectedResult'] = str_replace("'", "\"", $result['ExpectedResult']);
        }
        if (!is_null($result['ActualResponse'])) {
            $result['ActualResponse'] = str_replace("'", "\"", $result['ActualResponse']);
            // Optional: Try to encode to ensure it's valid JSON
            json_decode($result['ActualResponse']);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning("Invalid JSON in ActualResponse: " . json_last_error_msg());
                $result['ActualResponse'] = null; // or set a default/fallback value
            }
        }
    }
}


    public function render()
    {

        log::info('render testcase');
        return view('livewire.test-case-table-component');
    }
}
