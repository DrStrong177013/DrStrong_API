<div x-data="{
        filter: 'all',
        currentFilter: 'all',
        showModal: false,  // Modal visibility state
        selectedTestCase: null,  // Selected test case data
        updateFilter(newFilter) {
            this.currentFilter = this.filter;
            this.filter = newFilter;

            const rows = document.querySelectorAll('tr.result');
            rows.forEach(row => {
                row.style.opacity = '0';
                row.style.transition = 'opacity 0.3s ease';
            });

            setTimeout(() => {
                rows.forEach(row => {
                    row.style.display = (this.filter === 'all' || row.classList.contains(this.filter)) ? '' : 'none';
                    row.style.opacity = '1';
                });
            }, 300);
        },
        openModal(testCase) {
            console.log('Opening modal...');  // Debugging
            this.selectedTestCase = testCase;
            this.showModal = true;
        },
        closeModal() {
            console.log('Closing modal...');  // Debugging
            this.showModal = false;
            this.selectedTestCase = null;
        },
        hasVisibleRows() {
            const rows = document.querySelectorAll('tr.result');
            return Array.from(rows).some(row => row.style.display !== 'none');
        }
    }" @change-filter.window="updateFilter($event.detail.filter)" class="table-section">

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
        <tbody>
            @if(isset($results))
                @foreach ($results as $result)             
                    <tr class="{{ $loop->even ? 'even' : 'odd' }} result {{ strtolower($result['Result']) ?? 'default-class' }}"
                        @click="openModal({ 
                                                            result: '{{ $result['Result'] ?? 'N/A' }}',
                                                            refs: '{{ $result['Refs'] ?? 'N/A' }}',
                                                            method: '{{ strtoupper($result['Method']) ?? 'N/A' }}',
                                                            testcase: '{{ Str::limit($result['Testcase'], 85, '...') ?? 'N/A' }}',
                                                            status: '{{ $result['ActualStatusCode'] ?? 'N/A' }}'
                                                        })" style="cursor: pointer;">
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
                            {{ $result['ActualStatusCode'] ?? 'N/A' }}
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="5" class="no-results th_center">No results found</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div id="noResultsMessage" class="no-results-message" x-show="!hasVisibleRows()">No test case found with selected
        results.</div>

    <!-- Modal -->
    <div x-show="showModal" class="modal" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        style="display: none; position: fixed; top: 0; left: 0; z-index: 9999; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); align-items: center; justify-content: center;">
        <div class="modal-content"
            style="background-color: white; padding: 20px; border-radius: 8px; width: 400px; position: relative;">
            <span class="close" @click="closeModal"
                style="position: absolute; top: 10px; right: 20px; font-size: 24px; cursor: pointer;">&times;</span>
            <template x-if="selectedTestCase">
                <div>
                    <h2>Test Case Details</h2>
                    <p><strong>Result:</strong> <span x-text="selectedTestCase.result"></span></p>
                    <p><strong>Refs:</strong> <span x-text="selectedTestCase.refs"></span></p>
                    <p><strong>Method:</strong> <span x-text="selectedTestCase.method"></span></p>
                    <p><strong>Testcase:</strong> <span x-text="selectedTestCase.testcase"></span></p>
                    <p><strong>Status:</strong> <span x-text="selectedTestCase.status"></span></p>
                </div>
            </template>
        </div>
    </div>
</div>

@script
<script>



</script>
@endscript