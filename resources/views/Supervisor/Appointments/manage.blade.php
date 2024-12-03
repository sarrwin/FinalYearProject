@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Manage Appointments</h1>
    
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <table class="table table-bordered table-hover">
        <thead class="thead-light">
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Student</th>
                <th>Meeting Type</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($appointments as $appointment)
                <tr>
                    <td>
                        @if ($appointment->slot)
                            {{ \Carbon\Carbon::parse($appointment->slot->date)->format('d M Y') }}
                        @else
                            {{ \Carbon\Carbon::parse($appointment->date)->format('d M Y') }}
                        @endif
                    </td>
                    <td>
                        @if ($appointment->slot)
                            {{ \Carbon\Carbon::parse($appointment->slot->start_time)->format('h:i A') }} - 
                            {{ \Carbon\Carbon::parse($appointment->slot->end_time)->format('h:i A') }}
                        @else
                            {{ \Carbon\Carbon::parse($appointment->start_time)->format('h:i A') }} - 
                            {{ \Carbon\Carbon::parse($appointment->end_time)->format('h:i A') }}
                        @endif
                    </td>
                    <td>{{ $appointment->student->name }}</td>
                    <td>
                        @if ($appointment->project_id)
                            <span class="badge bg-info text-white">Project Meeting</span> 
                            <small>({{ $appointment->project->title }})</small>
                        @else
                            <span class="badge bg-secondary">General Meeting</span>
                        @endif
                    </td>
                    <td>{{ $appointment->request_reason }}</td>
                    <td>
                        <span class="badge 
                            @if ($appointment->status == 'pending') bg-warning 
                            @elseif ($appointment->status == 'accepted') bg-success 
                            @else bg-danger 
                            @endif">
                            {{ ucfirst($appointment->status) }}
                        </span>
                    </td>
                    <td>
                        <form action="{{ route('supervisor.appointments.updateStatus', $appointment->id) }}" method="POST" class="d-inline-block">
                            @csrf
                            <div class="d-flex align-items-center">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="status" value="accepted" id="accept-{{ $appointment->id }}" {{ $appointment->status == 'accepted' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="accept-{{ $appointment->id }}">Accept</label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="status" value="declined" id="decline-{{ $appointment->id }}" {{ $appointment->status == 'declined' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="decline-{{ $appointment->id }}">Decline</label>
                                </div>
                                <button type="submit" class="btn btn-sm btn-primary">Save</button>
                            </div>
                        </form>

                        @if ($appointment->status == 'declined')
                            <form action="{{ route('supervisor.appointments.updateStatus', $appointment->id) }}" method="POST" class="d-inline-block mt-2">
                                @csrf
                                <textarea name="decline_reason" class="form-control form-control-sm" placeholder="Reason for decline">{{ $appointment->decline_reason ?? '' }}</textarea>
                                <button type="submit" class="btn btn-sm btn-danger mt-1">Submit Reason</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No appointments to manage.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
