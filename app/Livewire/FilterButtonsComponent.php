<?php

namespace App\Livewire;

use Livewire\Component;

class FilterButtonsComponent extends Component
{
    public $results;
    protected $listeners = ['refreshChildComponent' => 'refresh'];

    public function mount($results)
    {

        $this->results = $results;

    }

    public function refresh($results)
    {
        // Logic để render lại hoặc thực hiện hành động cụ thể
        try {
            $this->results = $results;
        $this->render();
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    public function render()
    {
        return view('livewire.filter-buttons-component');
    }
}
