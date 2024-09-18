let selectedTestCases = {}; // Lưu trạng thái các test case đã chọn trên toàn bộ các trang

// Hàm xử lý chọn/deselect tất cả các test case
document.getElementById('selectAll').addEventListener('click', function () {
    let checkboxes = document.querySelectorAll('input[name="selected_cases[]"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
        selectedTestCases[checkbox.value] = this.checked; // Lưu trạng thái test case
    });
    console.log('Select All Clicked:', selectedTestCases); // Kiểm tra trạng thái sau khi chọn tất cả
    toggleRunButton(); // Gọi hàm để cập nhật trạng thái nút Run
});

// Hàm xử lý khi chọn/deselect từng test case
document.querySelectorAll('input[name="selected_cases[]"]').forEach(checkbox => {
    checkbox.addEventListener('change', function () {
        selectedTestCases[this.value] = this.checked; // Lưu trạng thái test case
        console.log('Checkbox Changed:', selectedTestCases); // Kiểm tra trạng thái sau khi thay đổi checkbox
        updateSelectAllState(); // Cập nhật lại trạng thái nút "Select All"
        toggleRunButton(); // Gọi hàm để cập nhật trạng thái nút Run
    });
});

// Hàm bật/tắt nút Run dựa trên số lượng test case đã chọn
function toggleRunButton() {
    const runButton = document.getElementById('runButton');
    const selectedTestCasesCount = Object.values(selectedTestCases).filter(val => val).length; // Đếm số test case đã chọn
    console.log('Selected Test Cases Count:', selectedTestCasesCount); // Kiểm tra số lượng test case đã chọn

    // Bật nút "Run" nếu có ít nhất một test case được chọn
    runButton.disabled = selectedTestCasesCount === 0;
}

// Hàm cập nhật trạng thái của "Select All"
function updateSelectAllState() {
    let currentCheckboxes = document.querySelectorAll('input[name="selected_cases[]"]');
    let allChecked = Array.from(currentCheckboxes).every(cb => cb.checked);
    let noneChecked = Array.from(currentCheckboxes).every(cb => !cb.checked);
    document.getElementById('selectAll').checked = allChecked;
    document.getElementById('selectAll').indeterminate = !allChecked && !noneChecked;
    console.log('Select All State Updated: allChecked:', allChecked, 'noneChecked:', noneChecked); // Kiểm tra trạng thái nút "Select All"
}

// Hàm render bảng test cases
function renderTable() {
    const tableBody = document.getElementById('testCasesTableBody');
    tableBody.innerHTML = ''; // Xóa nội dung hiện tại

    testCases.forEach((testCase, index) => {
        let isChecked = selectedTestCases[index + 1] ? 'checked' : ''; // Kiểm tra trạng thái đã lưu
        let row = `<tr class="test-case-row" data-index="${index + 1}">
                    <td><input type="checkbox" name="selected_cases[]" value="${index + 1}" ${isChecked}></td>`;

        testCase.forEach((data, key) => {
            if (headers[key] === 'Method') {
                row += `<td><span class="method-label" data-method="${data.toLowerCase()}">${data.toUpperCase()}</span></td>`;
            } else {
                row += `<td>${data}</td>`;
            }
        });

        row += '</tr>';
        tableBody.innerHTML += row;
    });

    setupRowClickEvent();  // Thêm sự kiện click cho các dòng mới
    updateSelectAllState(); // Cập nhật trạng thái của Select All
    toggleRunButton(); // Cập nhật lại nút "Run" theo các test case đã chọn
}

// Hàm xử lý khi click vào từng hàng của bảng (mở modal chi tiết)
function setupRowClickEvent() {
    document.querySelectorAll('.test-case-row').forEach(row => {
        row.addEventListener('click', function (event) {
            if (event.target.type === 'checkbox') {
                return; // Không xử lý nếu click vào checkbox
            }

            const index = this.getAttribute('data-index'); // Lấy chỉ số test case từ data-index
            const testCaseData = remainingTestCases[index - 1]; // Chỉ số mảng bắt đầu từ 0

            if (!testCaseData) {
                console.error('Test case data not found for index: ' + index);
                return;
            }

            const detailModalBody = document.querySelector('#detailModal .modal-body');
            detailModalBody.innerHTML = ''; // Xóa nội dung hiện tại của modal

            // Hiển thị các header và dữ liệu tương ứng
            remainingHeaders.forEach((header, key) => {
                const value = testCaseData[key] || 'N/A';
                detailModalBody.innerHTML += `<strong>${header}</strong>: ${value}<br>`;
            });

            const detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
            detailModal.show(); // Hiển thị modal
        });
    });
}

// Xử lý khi người dùng nhấn nút "Run"
document.getElementById('runButton').addEventListener('click', function () {
    let selectedCases = Object.keys(selectedTestCases).filter(key => selectedTestCases[key]); // Lấy các test case đã chọn

    if (selectedCases.length > 0) {
        // Gửi các test case đã được chọn để xử lý
        console.log('Running Selected Test Cases:', selectedCases); // Gửi các test case đã chọn (hoặc in ra console)
        // Gửi request với các test case đã chọn (tùy thuộc vào logic xử lý của bạn)
        // Ví dụ:
        // axios.post('/run-test-cases', { testCases: selectedCases })
        //     .then(response => console.log(response.data));
    } else {
        alert('No test case selected');
    }
});

// Gọi renderTable để hiển thị bảng ban đầu
renderTable();
toggleRunButton(); // Kiểm tra trạng thái nút Run khi khởi tạo
