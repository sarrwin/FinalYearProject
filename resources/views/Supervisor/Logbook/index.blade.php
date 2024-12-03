@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Logbooks for Project: {{ $project->title }}</h1>
    <button class="btn btn-primary" onclick="printLogbook()">Print Logbook</button>
    <br>
    <table class="table">
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Activity</th>
                <th>Date</th>
                <th>Verified</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($logbook->entries as $entry)
                <tr>
                    <td>{{ $entry->student->name }}</td>
                    <td>{{ $entry->activity }}</td>
                    <td>{{ \Carbon\Carbon::parse($entry->activity_date)->format('Y-m-d') }}</td>
                    <td>{{ $entry->verified ? 'Yes' : 'No' }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @if ($entry->logbookFiles)
                                @foreach ($entry->logbookFiles as $file)
                                    <a href="{{ Storage::url($file->file_path) }}" class="btn btn-info btn-sm" target="_blank">View File</a>
                                @endforeach
                            @endif

                            @if (!$entry->verified)
                                <form action="{{ route('supervisor.logbook.verify', $entry->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">Verify</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>


<script>
    function printLogbook() {
        window.print();
    }
</script>
@endsection
