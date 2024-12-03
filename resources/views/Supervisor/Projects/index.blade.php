@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Projects</h1>
    <a href="{{ route('supervisor.projects.create') }}" class="btn btn-primary mb-3">Create New Project</a>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Title</th>
                <th>Assigned Students</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($projects as $project)
                <tr>
                    <td>{{ $project->title }}</td>
                    <td>
                        @foreach($project->students as $student)
                            {{ $student->name }}@if(!$loop->last), @endif
                        @endforeach
                    </td>
                    <td>
                        @if (Auth::id() === $project->supervisor_id)
                            <a href="{{ route('supervisor.projects.edit', $project) }}" class="btn btn-secondary btn-sm">Edit</a>
                            <form action="{{ route('supervisor.projects.destroy', $project) }}" method="POST" class="d-inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
