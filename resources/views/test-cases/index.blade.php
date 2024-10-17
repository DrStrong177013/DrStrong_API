<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Case Display</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- <link rel="stylesheet" href="{{ asset('cssForTest/loading.css') }}"> -->
    <link rel="stylesheet" href="{{ asset('cssForTest/index.css') }}">
</head>

<body>
    <!-- @include('components.loading') -->
    <div class="container mt-4">
        <h2 class="text-center mb-4">Test Case Display</h2>
        <form method="POST" action="{{ route('sendTestCases') }}">
            @csrf
            <input type="hidden" name="file_path" value="{{ $filePath }}">

            @foreach ($headers as $header)
                <input type="hidden" name="headers[]" value="{{ $header }}">
            @endforeach

            <table class="table table-hover">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll" class="border-radius-left"></th>
                        {{-- <th>
                            <div>
                                <button id="selectAllCurrentPage">Chọn tất cả trên trang hiện tại</button>
                                <button id="selectAllPages">Chọn tất cả test case</button>
                            </div>
                        </th> --}}
                        @foreach ($headers as $header)
                            <th>{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody id="testCasesTableBody">
                    <!-- Test cases sẽ được hiển thị bằng JavaScript -->
                </tbody>

            </table>

            <div class="d-flex justify-content-between btn-action upload-sidebar">
                <button type="button" class="btn btn-secondary" onclick="window.history.back();">Cancel</button>
                <button type="submit" class="btn btn-run" id="runButton" disabled>Run</button>
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
    <script>
        var remainingTestCases = @json($remainingTestCases) || [];
        var remainingHeaders = @json($remainingHeaders) || [];
        var testCases = @json($testCases) || [];
        var headers = @json($headers) || [];
    </script>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- <script src="{{ asset('jsForTest/loading.js') }}"></script> -->
    <script src="{{ asset('jsForTest/index.js') }}"></script>
</body>

</html>