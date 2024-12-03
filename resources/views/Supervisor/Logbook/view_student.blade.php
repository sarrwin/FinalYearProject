@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ $student->name }}'s Logbooks</h1>
    <table class="table">
        <thead>
            <tr>
                <th>Project</th>
                <th>Activity</th>
                <th>Date</th>
                <th>Verified</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($logbookEntries as $entry)
                <tr>
                    <td>{{ $entry->logbook->project->title }}</td>
                    <td>{{ $entry->activity }}</td>
                    <td>{{ \Carbon\Carbon::parse($entry->activity_date)->format('Y-m-d') }}</td>
                    <td>{{ $entry->verified ? 'Yes' : 'No' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
