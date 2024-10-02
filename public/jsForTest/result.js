// public\jsForTest\result.js
let { failedCount, passedCount, untestedCount } = results.reduce((acc, result) => {
    if (result.Result === 'Passed') acc.passedCount++;
    else if (result.Result === 'Failed') acc.failedCount++;
    else acc.untestedCount++;
    return acc;
}, { failedCount: 0, passedCount: 0, untestedCount: 0 });

// Cập nhật số liệu tổng
document.getElementById('totalTests').innerText = results.length;

// Cập nhật số lượng các kết quả
document.getElementById('failedCount').innerText = failedCount;
document.getElementById('passedCount').innerText = passedCount;
document.getElementById('untestedCount').innerText = untestedCount;

// Tính phần trăm
let total = results.length;
if (total > 0) {
    document.getElementById('failedPercent').innerText = ((failedCount / total) * 100).toFixed(2) + '%';
    document.getElementById('passedPercent').innerText = ((passedCount / total) * 100).toFixed(2) + '%';
    document.getElementById('untestedPercent').innerText = ((untestedCount / total) * 100).toFixed(2) + '%';
} else {
    document.getElementById('failedPercent').innerText = '0%';
    document.getElementById('passedPercent').innerText = '0%';
    document.getElementById('untestedPercent').innerText = '0%';
}

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
            }
        }
    }
});

// Xử lý khi click vào test case trong bảng
document.addEventListener('DOMContentLoaded', () => {
    const tableRows = document.querySelectorAll('.test-case-table tbody tr');
    const modal = document.getElementById('testCaseModal');
    const closeBtn = document.querySelector('.modal .close');
    const modalBody = document.getElementById('modal-body');

    tableRows.forEach(row => {
        row.addEventListener('click', () => {
            const testcaseContent = row.querySelector('.testcase').textContent.trim();
            const result = results.find(item => item.Testcase === testcaseContent);

            if (result) {
                let formattedActualResponse = '';

                try {
                    if (typeof result['ActualResponse'] === 'string' && result['ActualResponse'].startsWith('{')) {
                        const actualResponseObject = JSON.parse(result['ActualResponse']);
                        formattedActualResponse = JSON.stringify(actualResponseObject, null, 4);
                    } else {
                        formattedActualResponse = result['ActualResponse'] || 'N/A';
                    }
                } catch (e) {
                    formattedActualResponse = result['ActualResponse'] || 'N/A';
                }

                // Cập nhật nội dung modal
                modalBody.innerHTML = `
                    <p><strong>Refs:</strong> ${result.Refs || 'N/A'}</p>
                    <p><strong>Testcase:</strong> ${result.Testcase || 'N/A'}</p>
                    <p><strong>EndPoint:</strong> ${result.EndPoint || 'N/A'}</p>
                    <p><strong>Method:</strong> ${result.Method || 'N/A'}</p>
                    <p><strong>Token:</strong> ${result.Token || 'N/A'}</p>
                    <p><strong>Body:</strong> ${result.Body || 'N/A'}</p>
                    <p><strong>StatusCode:</strong> ${result.StatusCode || 'N/A'}</p>
                    <p><strong>ExpectedResult:</strong> ${result.ExpectedResult || 'N/A'}</p>
                    <p><strong>ActualStatusCode:</strong> ${result.ActualStatusCode || 'N/A'}</p>
                    <p><strong>ActualResponse:</strong> <pre>${formattedActualResponse}</pre></p>
                    <p><strong>Result:</strong> ${result.Result || 'N/A'}</p>
                `;
                modal.style.display = 'block';
            }
        });
    });

    closeBtn.onclick = () => {
        modal.style.display = 'none';
    };

    window.onclick = (event) => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    };

    // Bộ lọc kết quả test case
    const filterButtons = document.querySelectorAll('.filter-button');
    const messageElement = document.getElementById('noResultsMessage');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            const filter = button.getAttribute('data-filter');
            filterTable(filter);
        });
    });

    function filterTable(filter) {
        let hasVisibleRows = false; 

        tableRows.forEach(row => {
            const resultCell = row.querySelector('.result');
            const isVisible = (filter === 'all' || resultCell.classList.contains(filter));
            row.style.display = isVisible ? '' : 'none';
            if (isVisible) hasVisibleRows = true;
        });

        messageElement.style.display = hasVisibleRows ? 'none' : 'block';
        if (!hasVisibleRows) {
            messageElement.innerText = `No test case found with ${filter} results.`;
        }
    }
});

