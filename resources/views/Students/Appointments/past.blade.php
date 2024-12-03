@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Past Appointments</h1>
    <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Supervisor</th>
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
                        @if ($appointment->slot && $appointment->slot->supervisor)
                            {{ $appointment->slot->supervisor->name }}
                        @elseif ($appointment->supervisor)
                            {{ $appointment->supervisor->name }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td>{{ $appointment->status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
