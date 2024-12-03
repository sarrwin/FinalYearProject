@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Appointments</h1>
    <div class="mb-3">
        @if (Auth::user()->isStudent())
            <a href="{{ route('student.appointments.upcoming') }}" class="btn btn-primary">Upcoming Appointments</a>
            <a href="{{ route('student.appointments.past') }}" class="btn btn-secondary">Past Appointments</a>
            <a href="{{ route('appointments.requestForm') }}" class="btn btn-secondary">Request Own Time</a>
        @elseif (Auth::user()->isSupervisor())
            <a href="{{ route('supervisor.appointments.upcoming') }}" class="btn btn-primary">Upcoming Appointments</a>
            <a href="{{ route('supervisor.appointments.past') }}" class="btn btn-secondary">Past Appointments</a>
        @endif
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>Slot</th>
                <th>Status</th>
                @if (Auth::user()->isSupervisor())
                    <th>Student</th>
                @endif
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($appointments as $appointment)
                <tr>
                    <td>
                        @if ($appointment->slot)
                            {{ $appointment->slot->date }} {{ $appointment->slot->start_time }} - {{ $appointment->slot->end_time }}
                        @else
                            {{ $appointment->date }} {{ $appointment->start_time }} - {{ $appointment->end_time }}
                        @endif
                    </td>
                    <td>{{ $appointment->status }}</td>
                    @if (Auth::user()->isSupervisor())
                        <td>{{ $appointment->student->name }}</td>
                    @endif
                    <td>
                        @if (Auth::user()->isSupervisor() && $appointment->status == 'pending')
                            <form action="{{ route('appointments.updateStatus', $appointment->id) }}" method="POST">
                                @csrf
                                <select name="status" onchange="this.form.submit()">
                                    <option value="accepted" {{ $appointment->status == 'accepted' ? 'selected' : '' }}>Accept</option>
                                    <option value="declined" {{ $appointment->status == 'declined' ? 'selected' : '' }}>Decline</option>
                                </select>
                            </form>
                        @elseif (Auth::user()->isStudent() && $appointment->status == 'pending')
                            <form action="{{ route('appointments.cancel', $appointment->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
