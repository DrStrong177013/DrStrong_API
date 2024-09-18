{{-- resources/views/test-cases/results.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Test Results</title>
    <link rel="stylesheet" href="{{ asset('cssForTest/loading.css') }}">
    <link rel="stylesheet" href="{{ asset('cssForTest/result.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="container">
        <div class="top-section">
            <div class="chart-container">
                <canvas id="testResultsChart"></canvas>
                <div class="chart-total">
                    <p>Total</p>
                    <h2 id="totalTests"></h2>
                </div>
            </div>

            <div>
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

        <div class="table-section">
            <table class="test-case-table">
                <thead>
                    <tr>
                        <th>Result</th>
                        <th class="th_center">Method</th>
                        <th>Testcase</th>
                        <th class="th_center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($results as $result)
                        <tr class="{{ $loop->even ? 'even' : 'odd' }}">
                            <td class="result {{ isset($result['Result']) ? strtolower($result['Result']) : 'default-class' }}"
                                width="50px">
                                <div class="div_results">{{ $result['Result'] ?? 'N/A' }}</div>
                            </td>

                            <td class="method method-label th_center" data-method="{{ strtolower($result['Method']) }}">
                                {{ strtoupper($result['Method']) ?? 'N/A' }}
                            </td>
                            <td class="testcase">
                                {{ Str::limit($result['Testcase'], 50, '...') ?? 'N/A'}}
                            </td>
                            <td class="status th_center" data-status="{{ $result['ActualStatusCode'] ?? 'N/A' }}">
                                {{ $result['ActualStatusCode'] ?? 'N/A' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <script>
        const results = {!! json_encode($results) !!};
    </script>

    <script src="{{ asset('jsForTest/result.js') }}"></script>
</body>

</html>