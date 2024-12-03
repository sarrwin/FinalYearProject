@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Slot</h1>
    <form action="{{ route('slots.update', $slot->id) }}" method="POST" id="slotForm">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="date" class="form-label">Date</label>
            <input type="date" name="date" id="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', $slot->date) }}" required>
            @error('date')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="invalid-feedback" id="dateError" style="display:none;">The selected date is in the past.</div>
        </div>
        <div class="mb-3">
            <label for="start_time" class="form-label">Start Time</label>
            <input type="time" name="start_time" id="start_time" class="form-control @error('start_time') is-invalid @enderror" value="{{ old('start_time', $slot->start_time) }}" required>
            @error('start_time')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="end_time" class="form-label">End Time</label>
            <input type="time" name="end_time" id="end_time" class="form-control @error('end_time') is-invalid @enderror" value="{{ old('end_time', $slot->end_time) }}" required>
            @error('end_time')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="meeting_details" class="form-label">Meeting Details</label>
            <input type="text" name="meeting_details" id="meeting_details" class="form-control @error('meeting_details') is-invalid @enderror" value="{{ old('meeting_details', $slot->meeting_details) }}">
            @error('meeting_details')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary">Update Slot</button>
    </form>
</div>

<script>
    document.getElementById('slotForm').addEventListener('submit', function(event) {
        var selectedDate = new Date(document.getElementById('date').value);
        var currentDate = new Date();
        currentDate.setHours(0, 0, 0, 0); // Set to midnight to only compare dates

        if (selectedDate < currentDate) {
            event.preventDefault();
            document.getElementById('date').classList.add('is-invalid');
            document.getElementById('dateError').style.display = 'block';
        } else {
            document.getElementById('date').classList.remove('is-invalid');
            document.getElementById('dateError').style.display = 'none';
        }
    });
</script>
@endsection
