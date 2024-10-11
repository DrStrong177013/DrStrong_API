<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Test Results</title>
    <link rel="stylesheet" href="{{ asset('cssForTest/result.css') }}">
    @livewireStyles
    
</head>
<body>


    @livewire('test-results-component')



    
    @livewireScripts


    @stack('scripts')
</body>

</html>