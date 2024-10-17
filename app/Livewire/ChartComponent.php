<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\TestCaseResult;

class ChartComponent extends Component
{
    protected $listeners = ['refreshChart' => 'updateChartData'];
    public $testResults;

    public function mount()
    {
        $this->updateChartData();
    }

    public function updateChartData()
    {
        \Log::info('updateChartData called');
        try {
            $this->testResults = TestCaseResult::all()->toArray();

            $failedCount = $this->getCount('Failed');
            $passedCount = $this->getCount('Passed');
            $untestedCount = $this->getCount('Untested');

            \Log::info('Dispatching updateChart with data:', [
                'failed' => $failedCount,
                'passed' => $passedCount,
                'untested' => $untestedCount,
            ]);


            $this->dispatch('updateChart', [
                'failed' => $failedCount,
                'passed' => $passedCount,
                'untested' => $untestedCount,
            ]);


        } catch (\Exception $e) {
            \Log::error('Error updating chart data: ' . $e->getMessage());
        }
    }



    public function getCount($result)
    {
        // Kiểm tra dữ liệu trước khi lọc
        \Log::info('Current test results:', $this->testResults);

        return count(array_filter($this->testResults, function ($testResult) use ($result) {
            return $testResult['Result'] === $result;
        }));
    }


    public function render()
    {
        $failedCount = $this->getCount('Failed');
        $passedCount = $this->getCount('Passed');
        $untestedCount = $this->getCount('Untested');

        return view('livewire.chart-component', [
            'failedCount' => $failedCount,
            'passedCount' => $passedCount,
            'untestedCount' => $untestedCount,
        ]);
    }

}
