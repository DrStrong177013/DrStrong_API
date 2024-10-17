<div>
    <div class="filter-section">
        <div class="filter-buttons" wire:ignore>
            <button wire:click="setFilter('all')" class="filter-button filter-button-all" data-filter="all">All</button>
            <button wire:click="setFilter('passed')" class="filter-button filter-button-passed" data-filter="passed">Passed</button>
            <button wire:click="setFilter('failed')" class="filter-button filter-button-failed" data-filter="failed">Failed</button>
            <button wire:click="setFilter('untested')" class="filter-button filter-button-untested"
                data-filter="untested">Untested</button>
        </div>
    </div>

    <div class="table-section">
        <table class="test-case-table">
            <thead>
                <tr>
                    <th class="th_center border-radius-left">Result</th>
                    <th>Refs</th>
                    <th class="th_center">Method</th>
                    <th>Testcase</th>
                    <th class="th_center border-radius-right">Status</th>
                </tr>
            </thead>
            <tbody class="tbody-testcase">
                @foreach ($filteredResults as $result)
                    <tr style="cursor: pointer;" wire:click="showModal({{ json_encode($result) }})">
                        <td class="result {{ strtolower($result['Result']) ?? 'default-class' }}" width="60px">
                            <div class="div_results th_center">{{ $result['Result'] ?? 'N/A' }}</div>
                        </td>
                        <td class="refs" width="70px">{{ $result['Refs'] ?? 'N/A'}}</td>
                        <td class="method method-label th_center" data-method="{{ strtolower($result['Method']) ?? 'N/A' }}"
                            width="100px">
                            {{ strtoupper($result['Method']) ?? 'N/A' }}
                        </td>
                        <td class="testcase">
                            {{ Str::limit($result['Testcase'], 85, '...') ?? 'N/A' }}
                        </td>
                        <td class="status th_center" data-status="{{ $result['ActualStatusCode'] ?? 'N/A' }}" width="100px">
                            <div>
                                {{ $result['ActualStatusCode'] ?? 'N/A' }}
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Modal -->

        <div id="modal" wire:ignore.self class="hidden">
            <div class="modal-content">
                <span>
                    <div class="close" wire:click="closeModal">&times;</div>
                </span>
                @if($selectedTestCase)
                    <h1 class="modal-info">
                        {{ $selectedTestCase['Refs'] ?? 'N/A' }}
                    </h1>
                    <div class="modal-info">
                        {{ $selectedTestCase['Testcase'] ?? 'N/A' }}
                    </div>
                    <div class="modal-info margin-top-30px">
                        <span class="modal-header">Result:</span>
                        {{ $selectedTestCase['Result'] ?? 'N/A' }}
                    </div>
                    <div class="modal-info">
                        <span class="modal-header">Method:</span>
                        {{ $selectedTestCase['Method'] ?? 'N/A' }}
                    </div>
                    <div class="modal-info">
                        <span class="modal-header">Endpoint:</span>
                        {{ $selectedTestCase['EndPoint'] ?? 'N/A' }}
                    </div>
                    <div class="modal-info">
                        <span class="modal-header">Body</span>
                        <p class="text_result_area">{{ $selectedTestCase['Body'] ?? 'N/A' }}</p>
                    </div>
                    <h2 class="margin-top-30px center">Expected</h2>
                    <div class="div_Expected">
                        <div class="modal-info">
                            <span class="modal-header">Result</span>
                            <pre class="text_result_area">{{ $selectedTestCase['ExpectedResult'] ?? 'N/A' }}</pre>
                        </div>
                        <div class="modal-info">
                            <span class="modal-header">Status:</span>
                            {{ $selectedTestCase['StatusCode'] ?? 'N/A' }}
                        </div>
                    </div>
                    <h2 class="margin-top-30px center">Actual</h2>
                    <div class="div_Actual">
                        <div class="modal-info">
                            <span class="modal-header ">Result</span>
                            <pre class="text_result_area">{{ $selectedTestCase['ActualResponse'] ?? 'N/A' }}</pre>
                        </div>
                        <div class="modal-info">
                            <span class="modal-header">Status:</span>
                            {{ $selectedTestCase['ActualStatusCode'] ?? 'N/A' }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@push('scripts')
    <script>



        //modal
        document.addEventListener('livewire:initialized', function () {
            Livewire.on('openModal', () => {
                let modal = document.getElementById('modal');

                // Kiểm tra và loại bỏ tất cả các lớp liên quan trước khi thêm lớp 'open'
                if (modal.classList.contains('hidden')) {
                    modal.classList.remove('hidden');
                    console.log('Removed hidden:', modal.classList);
                } else {
                    console.log('Modal did not have hidden class');
                }

                modal.classList.add('open');
                console.log('Added open:', modal.classList);

                console.log('Open modal');
            });

            Livewire.on('closeModal', () => {
                let modal = document.getElementById('modal');

                modal.classList.remove('open');
                console.log('Removed open:', modal.classList);

                setTimeout(() => {
                    modal.classList.add('hidden');
                    console.log('Added hidden:', modal.classList);
                }, 300); // Thời gian delay khớp với transition trong CSS
            });
        });
    </script>
@endpush