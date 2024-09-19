
let failedCount = 0;
let passedCount = 0;
let untestedCount = 0;


// Đếm số lượng
results.forEach(result => {
    if (result.Result === 'Passed') {
        passedCount++;
    } else if (result.Result === 'Failed') {
        failedCount++;
    } else {
        untestedCount++;
    }
});

// Cập nhật số liệu tổng
document.getElementById('totalTests').innerText = results.length;

// Cập nhật số lượng các kết quả
document.getElementById('failedCount').innerText = failedCount;
document.getElementById('passedCount').innerText = passedCount;
document.getElementById('untestedCount').innerText = untestedCount;

// Tính phần trăm
let total = results.length;
document.getElementById('failedPercent').innerText = ((failedCount / total) * 100).toFixed(2) + '%';
document.getElementById('passedPercent').innerText = ((passedCount / total) * 100).toFixed(2) + '%';
document.getElementById('untestedPercent').innerText = ((untestedCount / total) * 100).toFixed(2) + '%';

// Tạo biểu đồ Doughnut
const ctx = document.getElementById('testResultsChart').getContext('2d');
const chart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Failed', 'Passed', 'Untested'],
        datasets: [{
            label: 'Test Results',
            data: [failedCount, passedCount, untestedCount],
            backgroundColor: ['#e74c3c', '#2ecc71', '#3498db'],
            hoverOffset: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '70%',
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.7)',
                titleColor: '#fff',
                bodyColor: '#fff',
                titleFont: {
                    weight: 'bold',
                    size: 16
                },
                bodyFont: {
                    weight: 'bold',
                    size: 14
                },
                callbacks: {
                    label: function(tooltipItem) {
                        let label = tooltipItem.label || '';
                        if (label) {
                            label += ': ';
                        }
                        label += tooltipItem.raw; 
                        return label;
                    }
                }
            },
            datalabels: {
                display: true,
                color: '#fff',
                font: {
                    weight: 'bold',
                    size: 18
                },
                formatter: function(value, context) {
                    if (context.dataIndex === 1) {
                        return context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                    }
                    return '';
                }
            }
        }
    }
});
