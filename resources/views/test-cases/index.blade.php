<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Case Display</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .method-label {
        font-weight: bold;
        padding: 5px 10px;
        border-radius: 5px;
        color: white; /* Màu chữ chung */
    }

    .method-label[data-method="get"] {
        color: green; /* Màu chữ cho GET */
    }

    .method-label[data-method="post"] {
        color: orange; /* Màu chữ cho POST */
    }

    .method-label[data-method="delete"] {
        color: red; /* Màu chữ cho DELETE */
    }

    .method-label[data-method="put"] {
        color: blue; /* Màu chữ cho PUT */
    }
    </style>
</head>

<body>

    <div class="container mt-4">
        <h2 class="text-center mb-4">Test Case Display</h2>
        <form method="POST" action="/process-selected-test-cases">
            @csrf
            <input type="hidden" name="file_path" value="{{ $filePath }}">

            @foreach ($headers as $header)
                <input type="hidden" name="headers[]" value="{{ $header }}">
            @endforeach

            <table class="table table-hover">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll"> Select All</th>
                        @foreach ($headers as $header)
                            <th>{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($testCases as $index => $testCase)
                        <tr class="test-case-row" data-index="{{ $index }}">
                            <td><input type="checkbox" name="selected_cases[]" value="{{ $index + 1 }}"></td>
                            @foreach ($testCase as $key => $data)
                                @if ($headers[$key] === 'Method')
                                    <td><span class="method-label"
                                            data-method="{{ strtolower($data) }}">{{ strtoupper($data) }}</span></td>
                                @else
                                    <td>{{ $data }}</td>
                                @endif
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="d-flex justify-content-between btn-action">
                <button type="button" class="btn btn-secondary" onclick="window.history.back();">Cancel</button>
                <button type="submit" class="btn btn-primary">Run</button>
            </div>
        </form>
    </div>

    <!-- Modal hiển thị thông tin các cột còn lại -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Additional Test Case Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Nội dung của modal sẽ được cập nhật bằng JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS và Script -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Chuyển dữ liệu từ Blade vào JavaScript
        const remainingHeaders = @json($remainingHeaders);
        const remainingTestCases = @json($remainingTestCases);

        document.querySelectorAll('.test-case-row').forEach(row => {
            row.addEventListener('click', function(event) {
                // Kiểm tra xem có phải là click vào checkbox không
                if (event.target.type === 'checkbox') {
                    return; // Ngăn không mở modal nếu click vào checkbox
                }

                const index = this.getAttribute('data-index');
                const detailModalBody = document.querySelector('#detailModal .modal-body');
                detailModalBody.innerHTML = ''; // Reset nội dung modal

                // Hiển thị dữ liệu trong modal
                remainingHeaders.forEach((header, key) => {
                    const value = remainingTestCases[index][key] ||
                    'N/A'; // Default to 'N/A' if value is undefined
                    detailModalBody.innerHTML += `<strong>${header}</strong>: ${value}<br>`;
                });

                // Hiển thị modal
                const detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
                detailModal.show();
            });
        });

        document.getElementById('selectAll').addEventListener('click', function() {
            let checkboxes = document.querySelectorAll('input[name="selected_cases[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = this.checked);
        });

        document.querySelectorAll('input[name="selected_cases[]"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                let allCheckboxes = document.querySelectorAll('input[name="selected_cases[]"]');
                let allChecked = Array.from(allCheckboxes).every(cb => cb.checked);
                let noneChecked = Array.from(allCheckboxes).every(cb => !cb.checked);
                document.getElementById('selectAll').checked = allChecked;
                document.getElementById('selectAll').indeterminate = !allChecked && !noneChecked;
            });
        });
    </script>

</body>

</html>
