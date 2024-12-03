@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Create Weekly Slots</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('slots.createWeeklySlots') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="start_date">Start Date</label>
            <input type="date" id="start_date" name="start_date" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="end_date">End Date</label>
            <input type="date" id="end_date" name="end_date" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="day_of_week">Day of the Week</label>
            <select id="day_of_week" name="day_of_week" class="form-control" required>
                <option value="Monday">Monday</option>
                <option value="Tuesday">Tuesday</option>
                <option value="Wednesday">Wednesday</option>
                <option value="Thursday">Thursday</option>
                <option value="Friday">Friday</option>
                <option value="Saturday">Saturday</option>
                <option value="Sunday">Sunday</option>
            </select>
        </div>

        <div class="form-group">
            <label for="start_time">Start Time</label>
            <input type="time" id="start_time" name="start_time" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="end_time">End Time</label>
            <input type="time" id="end_time" name="end_time" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="meeting_details">Meeting Details (optional)</label>
            <textarea id="meeting_details" name="meeting_details" class="form-control" rows="3" maxlength="255"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Create Weekly Slots</button>
    </form>
</div>
@endsection
