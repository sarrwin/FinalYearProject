@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="text-center my-4">Supervisors</h1>

    <!-- Filter by Department -->
   
    <!-- Supervisors List -->
    <div class="card shadow p-4">
        <ul class="list-group">
            @forelse ($supervisors as $supervisor)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">{{ $supervisor->name }}</h5>
                        <p class="mb-0"><strong>Department:</strong> {{ $supervisor->department }}</p>
                    </div>
                    <div>
                        <a href="{{ route('appointments.slots', $supervisor->id) }}" class="btn btn-success btn-sm mx-1">Book Appointment</a>
                        <a href="{{ route('supervisor.profile.show', $supervisor->id) }}" class="btn btn-info btn-sm mx-1">View Profile</a>
                    </div>
                </li>
            @empty
                <li class="list-group-item text-center">No supervisors found in this department.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
