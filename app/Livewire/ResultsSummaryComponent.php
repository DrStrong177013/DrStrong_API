<?php

namespace App\Livewire;

use Livewire\Component;

class ResultsSummaryComponent extends Component
{
    // ResultsSummaryComponent.php
    
    public $failedCount, $passedCount, $untestedCount, $totalTests;
    protected $listeners = ['refreshResultSummary' => 'refresh'];

    public function mount($failedCount, $passedCount, $untestedCount, $totalTests)
    {
        $this->failedCount = $failedCount;
        $this->passedCount = $passedCount;
        $this->untestedCount = $untestedCount;
        $this->totalTests = $totalTests;
    }
    public function refresh($failedCount, $passedCount, $untestedCount, $totalTests)
    {
        $this->failedCount = $failedCount;
        $this->passedCount = $passedCount;
        $this->untestedCount = $untestedCount;
        $this->totalTests = $totalTests;
    }

    public function render()
    {
        return view('livewire.results-summary-component');
    }

}
