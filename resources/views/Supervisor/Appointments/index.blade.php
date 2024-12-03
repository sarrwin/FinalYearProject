<!-- resources/views/appointments/index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Appointments</h1>
    
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Upcoming Appointments</h5>
                    <p class="card-text">Check your upcoming appointments.</p>
                    <a href="{{ route('students.appointments.upcoming') }}" class="btn btn-primary">Upcoming Appointments</a>
                </div>
            </div>
        </div>
    <table class="table">
        <thead>
            <tr>
                <th>Slot</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($appointments as $appointment)
                <tr>
                    <td>{{ $appointment->slot->date }} {{ $appointment->slot->start_time }} - {{ $appointment->slot->end_time }}</td>
                    <td>{{ $appointment->status }}</td>
                    <td>
                        @if (Auth::user()->role == 'supervisor')
                            <form action="{{ route('appointments.updateStatus', $appointment->id) }}" method="POST">
                                @csrf
                                @method('POST')
                                <select name="status" onchange="this.form.submit()">
                                    <option value="pending" {{ $appointment->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="accepted" {{ $appointment->status == 'accepted' ? 'selected' : '' }}>Accepted</option>
                                    <option value="declined" {{ $appointment->status == 'declined' ? 'selected' : '' }}>Declined</option>
                                    <option value="completed" {{ $appointment->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                            </form>
                        @endif
                        @if (Auth::user()->role == 'student' && $appointment->status == 'pending')
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
