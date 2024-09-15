<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Test Results</title>
    <style>
        /* Style cho màn hình chờ */
        #loadingOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8); /* Nền trắng với độ mờ */
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999; /* Đặt lên trên cùng để luôn hiển thị */
        }
        
        #loadingOverlay .spinner-border {
            width: 3rem;
            height: 3rem;
            border-width: 0.4em;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
    </style>
</head>
<body>
    <!-- Màn hình chờ -->
    <div id="loadingOverlay">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <h1>API Test Results</h1>
    <table>
        <thead>
            <tr>
                <th>Test Case ID</th>
                <th>Result</th>
                <th>Error</th>
            </tr>
        </thead>
        <tbody>
            @foreach($results as $result)
                <tr>
                    <td>{{ $result['Testcase'] }}</td>
                    <td>{{ $result['Result'] }}</td>
                    <td>{{ $result['Error'] ?? 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <script>
        // Hiển thị màn hình chờ cho đến khi trang hoàn tất tải
        window.addEventListener('load', function() {
            document.getElementById('loadingOverlay').style.display = 'none';
        });
    </script>
</body>
</html>
