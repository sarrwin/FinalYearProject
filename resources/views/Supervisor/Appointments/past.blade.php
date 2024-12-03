@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Past Appointments (Supervisor)</h1>
    <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Student</th>
                <th>Reason</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($appointments as $appointment)
                <tr>
                    <td>
                        @if ($appointment->slot)
                            {{ $appointment->slot->date }}
                        @else
                            {{ $appointment->date }}
                        @endif
                    </td>
                    <td>
                        @if ($appointment->slot)
                            {{ $appointment->slot->start_time }} - {{ $appointment->slot->end_time }}
                        @else
                            {{ $appointment->start_time }} - {{ $appointment->end_time }}
                        @endif
                    </td>
                    <td>
                    @if ($appointment->student)
                            {{ $appointment->student->name }}
                        @else
                            No Student
                        @endif
                        </td>
                    <td>{{ $appointment->request_reason }}</td>
                    <td>{{ $appointment->status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
