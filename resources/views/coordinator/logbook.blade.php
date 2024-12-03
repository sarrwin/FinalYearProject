@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Logbook for Project: {{ $project->title }}</h1>
    <h2>Supervisor: {{ $project->supervisor->name }}</h2>

    @foreach ($project->logbooks as $logbook)
        @if ($logbook->entries->isNotEmpty())
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Activity</th>
                        <th>Date</th>
                        <th>Files</th>
                        <th>Verified</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($logbook->entries as $entry)
                        <tr>
                            <td>{{ $entry->student->name }}</td>
                            <td>{{ $entry->activity }}</td>
                            <td>{{ $entry->activity_date }}</td>
                            <td>
                                @foreach ($entry->logbookFiles as $file)
                                    <a href="{{ Storage::url($file->file_path) }}" class="btn btn-info btn-sm" target="_blank">View File</a>
                                @endforeach
                            </td>
                            <td>{{ $entry->verified ? 'Yes' : 'No' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No entries found for this logbook.</p>
        @endif
    @endforeach
</div>
@endsection
