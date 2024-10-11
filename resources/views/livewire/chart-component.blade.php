<div class="top-section" wire:ignore>
    <div>
        <div id="chart" class="div-chart"></div>
    </div>
    <table class="results-summary margin-top">
        <thead>
            <tr>
                <th width="120px">Status</th>
                <th class="center" width="80px">Count</th>
                <th class="right" width="80px">%</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><span class="dot red"></span>Failed</td>
                <td id="failedCount" class="center">0</td>
                <td id="failedPercent" class="right">--</td>
            </tr>
            <tr>
                <td><span class="dot green"></span>Passed</td>
                <td id="passedCount" class="center">0</td>
                <td id="passedPercent" class="right">--</td>
            </tr>
            <tr>
                <td><span class="dot blue"></span>Untested</td>
                <td id="untestedCount" class="center">0</td>
                <td id="untestedPercent" class="right">--</td>
            </tr>
        </tbody>
    </table>

</div>

@push('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/apexcharts@latest"></script>

    <script>
        let chart;
        document.addEventListener('DOMContentLoaded', function () {
            var options = {
                chart: {
                    type: 'donut',
                    height: '250px',
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                        animateGradually: {
                            enabled: true,
                            delay: 150,
                        },
                        dynamicAnimation: {
                            enabled: true,
                            speed: 350,
                        }
                    },
                },
                series: [0, 0, 0], // Dữ liệu khởi tạo
                labels: ['Failed', 'Passed', 'Untested'],
                colors: ['#e74c3c', '#2ecc71', '#3498db'],
                dataLabels: {
                    enabled: false,
                    formatter: function (val) {
                        return val;
                    },
                    style: {
                        fontSize: '14px',
                    },
                },
                tooltip: {
                    enabled: true,
                    style: {
                        fontSize: '12px',
                        fontFamily: 'Helvetica, Arial, sans-serif',
                    },
                    x: {
                        show: true, // Hiện tên nhãn
                    },
                    y: {
                        formatter: function (val) {
                            return val;
                        },
                    },
                },
                stroke: {
                    show: false,
                    width: 3,
                    colors: ['#fff'], // Màu viền cho biểu đồ
                },
                plotOptions: {
                    pie: {
                        expandOnClick: false,
                        dataLabels: {
                            offset: 20,
                        },
                        donut: {
                            size: '62%',
                            labels: {
                                show: true,
                                name: {},
                                value: {
                                    fontSize: '25px'
                                },
                                total: {
                                    show: true,
                                    fontSize: '19px'
                                }
                            }
                        }
                    },
                },
                legend: {
                    show: false,
                    position: 'bottom',
                    horizontalAlign: 'center',
                    fontSize: '14px',
                    fontFamily: 'Helvetica, Arial, sans-serif',
                    labels: {
                        colors: ['#000'], // Màu chữ cho legend
                    },
                    itemMargin: {
                        horizontal: 10,
                        vertical: 5,
                    },
                },
                grid: {
                    padding: {
                        left: 0,
                        right: 0,
                        top: 0,
                        bottom: 0,
                    },
                },
            };


            chart = new ApexCharts(document.querySelector("#chart"), options);
            chart.render();

            Livewire.on('updateChart', function (data) {
                const chartData = data[0];
                const totalTests = chartData.failed + chartData.passed + chartData.untested;

                console.log('Received chart data:', chartData); // Kiểm tra dữ liệu
                console.log('Total tests:', totalTests); // Kiểm tra tổng số test

                if (totalTests > 0) {
                    // Tính toán phần trăm
                    const failedPercentage = ((chartData.failed / totalTests) * 100).toFixed(2);
                    const passedPercentage = ((chartData.passed / totalTests) * 100).toFixed(2);
                    const untestedPercentage = ((chartData.untested / totalTests) * 100).toFixed(2);

                    // Cập nhật nội dung summary
                    document.getElementById('failedCount').innerText = chartData.failed;
                    document.getElementById('failedPercent').innerText = failedPercentage ;

                    document.getElementById('passedCount').innerText = chartData.passed;
                    document.getElementById('passedPercent').innerText = passedPercentage;

                    document.getElementById('untestedCount').innerText = chartData.untested;
                    document.getElementById('untestedPercent').innerText = untestedPercentage;
                    chart.updateSeries([chartData.failed, chartData.passed, chartData.untested]);
                } else {
                    // Nếu không có tổng số tests, đặt về 0
                    document.getElementById('failedCount').innerText = 0;
                    document.getElementById('failedPercent').innerText = '--';

                    document.getElementById('passedCount').innerText = 0;
                    document.getElementById('passedPercent').innerText = '--';

                    document.getElementById('untestedCount').innerText = 0;
                    document.getElementById('untestedPercent').innerText = '--';
                }
            });

        });
    </script>
@endpush