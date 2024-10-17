<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Log;

class TestCaseTableComponent extends Component
{
    public $results;
    public $selectedTestCase = null;
    public $isModalOpen = false;
    public $filter = 'all';

    protected $listeners = ['refreshChildComponent' => 'refresh', 'updateFilter' => 'setFilter'];

    public function mount($results)
    {
        Log::info('mount testcase');
        $this->results = $results;
    }

    public function refresh($results)
    {
        try {
            $this->results = $results;
            Log::info('refresh testcase');
        } catch (\Throwable $th) {
            // Xử lý lỗi nếu cần
        }
    }

    public function setFilter($filter)
    {
        $this->filter = $filter; // Cập nhật filter
        $this->dispatch('filterUpdated', $this->filter); // Phát sự kiện cập nhật filter
        logger('filterUpdated');
    }

    public function showModal($testCase)
{
    if ($testCase) {
        $this->selectedTestCase = $testCase;
    
        $this->dispatch('openModal');
    } else {
        Log::error('Test case is null');
    }
}


    public function closeModal()
    {
        $this->dispatch('closeModal'); 
    }

    public function render()
    {
        Log::info('render testcase');
        return view('livewire.test-case-table-component', [
            'filteredResults' => $this->getFilteredResults()
        ]);
    }

    private function getFilteredResults()
    {
        // Lọc kết quả theo filter
        if ($this->filter === 'all') {
            return $this->results;
        }
        return array_filter($this->results, function ($result) {
            return strtolower($result['Result']) === strtolower($this->filter);
        });
    }
}
