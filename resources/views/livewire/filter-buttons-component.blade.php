<div class="filter-section" x-data="{}">
    <button @click="$dispatch('change-filter', { filter: 'all' })" class="filter-button-all" data-filter="all">All</button>
    <button @click="$dispatch('change-filter', { filter: 'passed' })" class="filter-button-passed" data-filter="passed">Passed</button>
    <button @click="$dispatch('change-filter', { filter: 'failed' })" class="filter-button-failed" data-filter="failed">Failed</button>
    <button @click="$dispatch('change-filter', { filter: 'untested' })" class="filter-button-untested" data-filter="untested">Untested</button>
</div>