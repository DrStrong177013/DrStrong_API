<div class="container" wire:poll.10s="refreshChart">

        @livewire('chart-component', ['results' => $results])
  
    
    @livewire('filter-buttons-component', ['results' => $results])
    
    @livewire('test-case-table-component', ['results' => $results])
</div>
