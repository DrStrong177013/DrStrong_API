<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Automation Test</title>
    <link rel="stylesheet" href="{{ asset('cssForTest/testUp.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <header>API Automation Test</header>
    <form action="{{ route('uploadTestCases') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div id="drop-zone" class="drop-zone">
            <i class="fas fa-cloud-upload-alt"></i>
            <p>Select a file or drag and drop here</p>
            <p class="file-info">.csv, .xls, .xlsx file not larger than 10mb</p>
            <button type="button" id="select-file-btn">SELECT FILE</button>
            <input type="file" name="excel_file" id="excel_file" required hidden>
        </div>

        <div><section class="progress-area"></section></div>

        <div class="upload-sidebar">
            <button type="submit" class="btn-upload" disabled>Upload</button>
        </div>
    </form>

    <script src="{{ asset('jsForTest/testUp.js') }}"></script>
</body>
</html>

