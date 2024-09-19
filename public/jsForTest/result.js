let failedCount = 0;
let passedCount = 0;
let untestedCount = 0;

// Đếm số lượng các kết quả
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
                    if (typeof result['Actual Response'] === 'string' && result['Actual Response'].startsWith('{')) {
                        const actualResponseObject = JSON.parse(result['Actual Response']);
                        formattedActualResponse = JSON.stringify(actualResponseObject, null, 4);
                    } else {
                        formattedActualResponse = result['Actual Response'] || 'N/A';
                    }
                } catch (e) {
                    formattedActualResponse = result['Actual Response'] || 'N/A';
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
                    <p><strong>Actual Response:</strong> <pre>${formattedActualResponse}</pre></p>
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
});

// Bộ lọc kết quả test case
document.addEventListener('DOMContentLoaded', () => {
    const filterButtons = document.querySelectorAll('.filter-button');
    const tableRows = document.querySelectorAll('.test-case-table tbody tr');

    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            const filter = button.getAttribute('data-filter');
            filterTable(filter);
        });
    });

    function filterTable(filter) {
        let hasVisibleRows = false; // Biến kiểm tra xem có hàng nào hiển thị không
        tableRows.forEach(row => {
            const resultCell = row.querySelector('.result');
            if (filter === 'all' || resultCell.classList.contains(filter)) {
                row.style.display = '';
                hasVisibleRows = true;
            } else {
                row.style.display = 'none';
            }
        });

        const messageElement = document.getElementById('noTestCasesMessage');
        if (!hasVisibleRows) {
            messageElement.innerText = `Don't have any test case with ${filter} Results`;
            messageElement.style.display = 'block';
        } else {
            messageElement.style.display = 'none';
        }
    }
});

