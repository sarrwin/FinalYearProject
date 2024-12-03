@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="text-center my-4">Students in Your Department</h1>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Project Title</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $index => $student)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $student->name }}</td>
                        <td>{{ $student->email }}</td>
                        <td>{{ $student->department }}</td>
                        <td>{{ $student->project->title ?? 'Not Assigned' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No students found in your department.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
