<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Automation Test</title>
    <link rel="stylesheet" href="{{ asset('cssForTest/testUp.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous" />
</head>
<body>
    <header>API Automation Test</header>
    <form class="mb-5" action="{{ route('uploadTestCases') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div id="drop-zone" class="drop-zone">
            <i class="fas fa-cloud-upload-alt mb-5"></i>
            <p>Select a file or drag and drop here</p>
            <p class="file-info mb-1">.csv, .xls, .xlsx file not larger than 10mb</p>
            <button type="button" id="select-file-btn">SELECT FILE</button>
            <input type="file" name="excel_file" id="excel_file" required hidden>
        </div>

        <div><section class="progress-area"></section></div>

        <div class="upload-sidebar">
            <button type="submit" class="btn-upload" disabled>Next</button>
        </div>
    </form>

    <script src="{{ asset('jsForTest/testUp.js') }}"></script>
</body>
</html>

