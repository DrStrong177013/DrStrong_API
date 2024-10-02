{{-- resources/views/test-cases/results.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Test Results</title>
    <!-- <link rel="stylesheet" href="{{ asset('cssForTest/loading.css') }}"> -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="{{ asset('cssForTest/result.css') }}">
</head>

<body>

    <!-- @include('components.loading') -->

    <div class="container">
        <div class="top-section">
            <div class="chart-container">
                <canvas id="testResultsChart"></canvas>
                <div class="chart-total">
                    <p>Total</p>
                    <h2 id="totalTests"></h2>
                </div>
            </div>

            <div class="results-summary">
                <table class="results-summary">
                    <thead>
                        <tr>
                            <th>Results</th>
                            <th class="th_center">Issues</th>
                            <th class="percent_th">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="dot red"></span> Failed</td>
                            <td id="failedCount" class="th_center"></td>
                            <td id="failedPercent" class="percent_th"></td>
                        </tr>
                        <tr>
                            <td><span class="dot green"></span> Passed</td>
                            <td id="passedCount" class="th_center"></td>
                            <td id="passedPercent" class="percent_th"></td>
                        </tr>
                        <tr>
                            <td><span class="dot blue"></span> Untested</td>
                            <td id="untestedCount" class="th_center"></td>
                            <td id="untestedPercent" class="percent_th"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="filter-section">
            <button class="filter-button" data-filter="all">All</button>
            <button class="filter-button" data-filter="passed">Passed</button>
            <button class="filter-button" data-filter="failed">Failed</button>
            <button class="filter-button" data-filter="untested">Untested</button>
        </div>

        <div class="table-section">

            <div id="resultsTable">
                @if (empty($results))
                    <p>No results available yet.</p>
                @else
                    <table class="test-case-table">
                        <thead>
                            <tr>
                                <th>Refs</th>
                                <th class="th_center">Result</th>
                                <th class="th_center">Method</th>
                                <th>Testcase</th>
                                <th class="th_center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($results as $result)
                                <tr class="{{ $loop->even ? 'even' : 'odd' }}">
                                    <td class="refs">{{ $result['Refs'] ?? 'N/A' }}</td>
                                    <td class="result {{ strtolower($result['Result']) ?? 'default-class' }}">
                                        <div class="div_results">{{ $result['Result'] ?? 'N/A' }}</div>
                                    </td>
                                    <td class="method method-label th_center"
                                        data-method="{{ strtolower($result['Method']) ?? 'N/A' }}">
                                        {{ strtoupper($result['Method']) ?? 'N/A' }}
                                    </td>
                                    <td class="testcase">
                                        {{ Str::limit($result['Testcase'], 85, '...') ?? 'N/A' }}
                                    </td>
                                    <td class="status th_center" data-status="{{ $result['ActualStatusCode'] ?? 'N/A' }}">
                                        {{ $result['ActualStatusCode'] ?? 'N/A' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                @endif
            </div>
            <div id="noResultsMessage" class="no-results-message"></div>
        </div>
    </div>
    <!-- Modal -->
    <div id="testCaseModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="modal-body">
                <!-- Nội dung modal sẽ được cập nhật bằng JavaScript -->
                <!-- Phần quan trọng là trong nội dung modal,sử dụng thẻ <pre> để giữ nguyên định dạng xuống dòng và thụt lề khi hiển thị JSON đã được format. -->
                <p><strong>Actual Response:</strong>
                <pre>${formattedActualResponse}</pre>
                </p>

            </div>
        </div>
    </div>

    @if (!empty($results))
        <script>
            const results = Object.values({!! json_encode($results) !!});
        </script>
    @endif
    <script src="{{ asset('jsForTest/result.js') }}"></script>
    <!-- <script>var exports = {};</script>




</body>

</html>