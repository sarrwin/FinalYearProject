@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ $supervisor->name }}</h1>
    <p>Email: {{ $supervisor->email }}</p>
    <a href="{{ route('appointments.slots', $supervisor->supervisor_id) }}" class="btn btn-primary">Book Appointment</a>
</div>
@endsection
