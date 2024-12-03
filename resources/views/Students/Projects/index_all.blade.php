@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="text-center my-4">All Projects</h1>

    @if ($projects->isEmpty())
        <div class="alert alert-info text-center">
            <strong>No projects found.</strong> Please check back later.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
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
                            <td class="fw-bold">{{ $project->title }}</td>
                            <td>{{ $project->supervisor->name }}</td>
                            <td class="text-center">{{ $project->students_required }}</td>
                            <td>
                                @if ($project->students->isEmpty())
                                    <span class="text-muted">No students assigned</span>
                                @else
                                    <ul class="list-unstyled mb-0">
                                        @foreach ($project->students as $student)
                                            <li>{{ $student->name }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('students.projects.details', $project->id) }}" class="btn btn-primary btn-sm">View Details</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $projects->links() }}
        </div>
    @endif
</div>
@endsection
