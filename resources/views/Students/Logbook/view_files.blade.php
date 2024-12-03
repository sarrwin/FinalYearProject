@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Files for Logbook Entry</h1>
    <h3>Activity: {{ $logbookEntry->activity }}</h3>
    <h3>Date: {{ \Carbon\Carbon::parse($logbookEntry->activity_date)->format('Y-m-d') }}</h3>
    <ul>
        @foreach ($files as $file)
            <li>
                <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank">{{ $file->file_path }}</a>
            </li>
        @endforeach
    </ul>
    <a href="{{ route('students.logbook.index') }}" class="btn btn-secondary">Back to Logbooks</a>
</div>
@endsection
