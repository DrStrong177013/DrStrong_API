<form method="POST" action="/process-selected-test-cases">
    @csrf
    <!-- Trường ẩn để lưu đường dẫn file -->
    <input type="hidden" name="file_path" value="{{ $filePath }}">

    <!-- Trường ẩn để lưu headers -->
    @foreach($headers as $header)
        <input type="hidden" name="headers[]" value="{{ $header }}">
    @endforeach

    <table class="table table-bordered">
        <thead>
            <tr>
                <th><input type="checkbox" id="selectAll"> Select All</th>
                @foreach($headers as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($testCases as $index => $testCase)
                <tr>
                    <td><input type="checkbox" name="selected_cases[]" value="{{ $index + 1 }}"></td>
                    @foreach($testCase as $data)
                        <td>{{ $data }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
    <button type="submit" class="btn btn-primary">Process Selected Test Cases</button>
</form>

<script>
    document.getElementById('selectAll').addEventListener('click', function() {
        let checkboxes = document.querySelectorAll('input[name="selected_cases[]"]');
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    });

    // Update 'Select All' checkbox based on individual checkboxes
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
