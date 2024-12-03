@extends('layouts.app')

@section('content')
    <h1>Book Appointment with {{ $slot->supervisor->name }}</h1>
    <form action="{{ route('appointments.store', $slot) }}" method="POST">
        @csrf
        <div class="form-group">
            <p>Slot: {{ $slot->date }} {{ $slot->start_time }} - {{ $slot->end_time }}-{{ $slot->end_time }}</p>
        </div>
        <div class="form-group">
            <label for="request_reason">Reason:</label>
            <textarea name="request_reason" id="request_reason" class="form-control" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Book</button>
    </form>
@endsection
