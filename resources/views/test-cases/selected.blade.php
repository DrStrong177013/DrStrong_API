@extends('layouts.app')

@section('content')
    <h1>Test Cases Selected</h1>
    <table class="table table-bordered">
        <thead>
            <tr>
                @foreach($headers as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($testCases as $testCase)
                <tr>
                    @foreach($testCase as $data)
                        <td>{{ $data }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headers) }}">No test cases selected.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
