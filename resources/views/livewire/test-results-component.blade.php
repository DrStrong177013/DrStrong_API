<div class="container" @if(!$jobCompleted) wire:poll.300ms="refreshChart" @endif>
    @livewire('chart-component', ['results' => $results])
    @livewire('test-case-table-component', ['results' => $results])
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
    Livewire.on('jobCompleted', (jobCompleted) => {
        // Nếu jobCompleted là mảng, lấy giá trị đầu tiên
        if (Array.isArray(jobCompleted)) {
            jobCompleted = jobCompleted[0];
        }

        if (jobCompleted == true) {
            const component = Livewire.find(document.querySelector('.container').getAttribute('wire:id'));
            component.set('jobCompleted', jobCompleted);
            console.log('jobCompleted:', jobCompleted);
        }
    });
});

</script>
