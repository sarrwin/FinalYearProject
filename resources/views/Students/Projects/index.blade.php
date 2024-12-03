@extends('layouts.app')

@section('content')
<div class="container">
    <h1>All Projects</h1>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Title</th>
                <th>Supervisor</th>
                <th>Number of Students Required</th>
                <th>Assigned Students</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($projects as $project)
                <tr>
                    <td>{{ $project->title }}</td>
                    <td>{{ $project->supervisor->name }}</td>
                    <td>{{ $project->students_required }}</td>
                    <td>
                        @foreach ($project->students as $student)
                            {{ $student->name }}@if (!$loop->last), @endif
                        @endforeach
                    </td>
                    <td>
                        <a href="{{ route('projects.details', $project->id) }}" class="btn btn-primary">View Details</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
