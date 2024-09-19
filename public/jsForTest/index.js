let selectedTestCases = {}; // Lưu trạng thái các test case đã chọn trên toàn bộ các trang

// Hàm xử lý chọn/deselect tất cả các test case
document.getElementById('selectAll').addEventListener('click', function () {
    let checkboxes = document.querySelectorAll('input[name="selected_cases[]"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
        selectedTestCases[checkbox.value] = this.checked; // Lưu trạng thái test case
    });
    toggleRunButton(); // Gọi hàm để cập nhật trạng thái nút Run
});

// Hàm xử lý khi chọn/deselect từng test case
function setupCheckboxEvents() {
    document.querySelectorAll('input[name="selected_cases[]"]').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            selectedTestCases[this.value] = this.checked; // Lưu trạng thái test case
            console.log('Updated selectedTestCases:', selectedTestCases); // Debug: Kiểm tra trạng thái của selectedTestCases
            updateSelectAllState(); // Cập nhật lại trạng thái nút "Select All"
            toggleRunButton(); // Gọi hàm để cập nhật trạng thái nút Run
        });
    });
}

// Hàm bật/tắt nút Run dựa trên số lượng test case đã chọn
// Hàm bật/tắt nút Run dựa trên số lượng test case đã chọn
function toggleRunButton() {
    const runButton = document.getElementById('runButton');
    const selectedTestCasesCount = Object.values(selectedTestCases).filter(val => val).length; // Đếm số test case đã chọn

    // Nếu có ít nhất một test case được chọn, bật nút Run, ngược lại disable nó
    if (selectedTestCasesCount > 0) {
        runButton.disabled = false; // Bật nút Run
        runButton.style.cursor = "pointer"; // Đổi con trỏ chuột khi có thể nhấn
        runButton.style.backgroundColor = "#398dd7"; // Đổi màu nền
    } else {
        runButton.disabled = true; // Disable nút Run
        runButton.style.cursor = "not-allowed"; // Con trỏ chuột không thể nhấn
        runButton.style.backgroundColor = "#ccc"; // Đổi màu nền sang xám
    }
}

// Hàm cập nhật trạng thái của "Select All"
function updateSelectAllState() {
    let currentCheckboxes = document.querySelectorAll('input[name="selected_cases[]"]');
    let allChecked = Array.from(currentCheckboxes).every(cb => cb.checked);
    let noneChecked = Array.from(currentCheckboxes).every(cb => !cb.checked);
    document.getElementById('selectAll').checked = allChecked;
    document.getElementById('selectAll').indeterminate = !allChecked && !noneChecked;
}

// Hàm render bảng test cases
function renderTable() {
    const tableBody = document.getElementById('testCasesTableBody');
    const fragment = document.createDocumentFragment(); // Sử dụng DocumentFragment để giảm thay đổi DOM

    testCases.forEach((testCase, index) => {
        let isChecked = selectedTestCases[index + 1] ? 'checked' : ''; // Kiểm tra trạng thái đã lưu
        let row = document.createElement('tr');
        row.classList.add('test-case-row');
        row.setAttribute('data-index', index + 1);

        let checkboxCell = document.createElement('td');
        checkboxCell.innerHTML = `<input type="checkbox" name="selected_cases[]" value="${index + 1}" ${isChecked}>`;
        row.appendChild(checkboxCell);

        testCase.forEach((data, key) => {
            let cell = document.createElement('td');
            if (headers[key] === 'Method') {
                cell.innerHTML = `<span class="method-label" data-method="${data.toLowerCase()}">${data.toUpperCase()}</span>`;
            } else {
                cell.textContent = data;
            }
            row.appendChild(cell);
        });

        fragment.appendChild(row);
    });

    tableBody.innerHTML = ''; // Xóa nội dung hiện tại
    tableBody.appendChild(fragment); // Thêm nội dung mới
    setupRowClickEvent();  // Thêm sự kiện click cho các dòng mới
    setupCheckboxEvents(); // Thiết lập lại sự kiện cho checkbox
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
        console.log('Running Selected Test Cases:', selectedCases); // Gửi các test case đã chọn (hoặc in ra console)
        // Gửi request với các test case đã chọn (tùy thuộc vào logic xử lý của bạn)
        // axios.post('/run-test-cases', { testCases: selectedCases })
        //     .then(response => console.log(response.data));
    } else {
        alert('No test case selected');
    }
});

// Gọi renderTable để hiển thị bảng ban đầu
renderTable();
toggleRunButton(); // Kiểm tra trạng thái nút Run khi khởi tạo
