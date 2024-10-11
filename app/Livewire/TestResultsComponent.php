<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\TestCaseResult; 
use Illuminate\Support\Facades\Log;
class TestResultsComponent extends Component
{
    public $results;
    protected $listeners = ['jobCompleted' => 'refreshResults'];


    public function mount()
    {
        // Lấy dữ liệu từ cơ sở dữ liệu
        log::info('mount results');
        $this->results = TestCaseResult::all()->toArray(); // Chuyển thành mảng

    }


    public function refreshChart(){
        log::info('refresh results');
        $this->results = TestCaseResult::all()->toArray();
        $this->dispatch('refreshChildComponent',$this->results); 
        $this->dispatch('refreshChart'); 
    }


    public function render()
    {     
        $results = $this->results;
        log::info('render results');
        return view('livewire.test-results-component');
    }
}
