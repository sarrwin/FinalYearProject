@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Overall Assigned Projects</h1>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Supervisor</th>
                <th>Student</th>
                <th>Projects</th>
                <th>Department</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($projects as $project)
            <tr>
                <td>{{ $project->supervisor->name ?? 'No Supervisor' }}</td>
                <td>
                    @foreach ($project->students as $student)
                        {{ $student->name }}@if (!$loop->last), @endif
                    @endforeach
                </td>
                <td>{{ $project->title }}</td>
                <td>{{ $project->department }}</td>
                <td>
                    <!-- Button to trigger modal -->
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#projectDetailsModal-{{ $project->id }}">
                        View Records
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Modal -->
    @foreach($projects as $project)
    <div class="modal fade" id="projectDetailsModal-{{ $project->id }}" tabindex="-1" aria-labelledby="projectDetailsLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="projectDetailsLabel">Project Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
    <p><strong>Project Name:</strong> {{ $project->title }}</p>
    <p><strong>Supervisor:</strong> {{ $project->supervisor->name ?? 'No Supervisor' }}</p>
    <p><strong>Students:</strong>
        @foreach ($project->students as $student)
            {{ $student->name }}@if (!$loop->last), @endif
        @endforeach
    </p>
    <p><strong>Department:</strong> {{ $project->department }}</p>
    <p><strong>Description:</strong> {{ $project->description }}</p>
    
    <!-- Appointment Records -->
    <h5>Appointment Records</h5>
    @php
        $projectAppointments = $appointments->filter(function ($appointment) use ($project) {
            return $appointment->student_id && $appointment->supervisor_id && in_array($appointment->student_id, $project->students->pluck('id')->toArray());
        });
    @endphp
    @if ($projectAppointments->isEmpty())
        <p>No appointments found.</p>
    @else
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Appointment Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($projectAppointments as $appointment)
                    <tr>
                        <td>{{ $appointment->student->name }}</td>
                        <td>{{ $appointment->date }}</td>
                        <td>{{ $appointment->status }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Button to navigate to the logbook -->
    <a href="{{ route('coordinator.logbook', $project->id) }}" class="btn btn-info mt-3">
    View Logbook
</a>
</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
