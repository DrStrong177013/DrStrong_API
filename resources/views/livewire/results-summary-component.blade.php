<div>
<thead>
    <tr>
        <th>Status</th>
        <th>Count</th>
        <th>Percentage</th>
    </tr>
</thead>
<tbody>
    <tr>
        <td><span class="dot red"></span> Failed</td>
        <td class="th_center">{{ $failedCount }}</td>
        <td class="percent_th">
            {{ $totalTests > 0 ? number_format(($failedCount / $totalTests) * 100, 2) : '0.00' }}%
        </td>
    </tr>
    <tr>
        <td><span class="dot green"></span> Passed</td>
        <td class="th_center">{{ $passedCount }}</td>
        <td class="percent_th">
            {{ $totalTests > 0 ? number_format(($passedCount / $totalTests) * 100, 2) : '0.00' }}%
        </td>
    </tr>
    <tr>
        <td><span class="dot blue"></span> Untested</td>
        <td class="th_center">{{ $untestedCount }}</td>
        <td class="percent_th">
            {{ $totalTests > 0 ? number_format(($untestedCount / $totalTests) * 100, 2) : '0.00' }}%
        </td>
    </tr>
</tbody>

</div>