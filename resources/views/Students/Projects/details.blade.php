@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ $project->title }}</h1>
    <table class="table table-bordered">
        <tbody>
            <tr>
                <th>Description</th>
                <td>{{ $project->description }}</td>
            </tr>
            <tr>
                <th>Supervisor</th>
                <td>{{ $project->supervisor->name }}</td>
            </tr>
            <tr>
                <th>Number of Students Required</th>
                <td>{{ $project->students_required }}</td>
            </tr>
            <tr>
                <th>Assigned Students</th>
                <td>
                    @foreach ($project->students as $student)
                        {{ $student->name }}@if (!$loop->last), @endif
                    @endforeach
                </td>
            </tr>
            <tr>
                <th>Session</th>
                <td>{{ $project->session }}</td>
            </tr>
            <tr>
                <th>Department</th>
                <td>{{ $project->department }}</td>
            </tr>
            <tr>
                <th>Tools</th>
                <td>{{ $project->tools }}</td>
            </tr>
        </tbody>
    </table>
    <a href="{{ route('students.projects.index_all') }}" class="btn btn-primary">Back to All Projects</a>
</div>
@endsection
