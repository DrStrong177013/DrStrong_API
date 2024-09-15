// index.js
document.getElementById('selectAll').addEventListener('click', function() {
    let checkboxes = document.querySelectorAll('input[name="selected_cases[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    toggleRunButton();
});

document.querySelectorAll('input[name="selected_cases[]"]').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        let allCheckboxes = document.querySelectorAll('input[name="selected_cases[]"]');
        let allChecked = Array.from(allCheckboxes).every(cb => cb.checked);
        let noneChecked = Array.from(allCheckboxes).every(cb => !cb.checked);
        document.getElementById('selectAll').checked = allChecked;
        document.getElementById('selectAll').indeterminate = !allChecked && !noneChecked;
        toggleRunButton();
    });
});

function toggleRunButton() {
    const selectedCheckboxes = document.querySelectorAll('input[name="selected_cases[]"]:checked');
    document.getElementById('runButton').disabled = selectedCheckboxes.length === 0;
}

document.querySelectorAll('.test-case-row').forEach(row => {
    row.addEventListener('click', function(event) {
        if (event.target.type === 'checkbox') {
            return;
        }

        const index = this.getAttribute('data-index');
        const detailModalBody = document.querySelector('#detailModal .modal-body');
        detailModalBody.innerHTML = '';

        remainingHeaders.forEach((header, key) => {
            const value = remainingTestCases[index][key] || 'N/A';
            detailModalBody.innerHTML += `<strong>${header}</strong>: ${value}<br>`;
        });

        const detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
        detailModal.show();
    });
});
