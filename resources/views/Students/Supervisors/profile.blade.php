@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Supervisor Profile</h1>
    <div class="card">
        <div class="card-header">
            {{ $supervisor->name }}
        </div>
        <div class="card-body">
            <p><strong>Contact Number:</strong> {{ $supervisor->contact_number }}</p>
            <p><strong>Office Address:</strong> {{ $supervisor->office_address }}</p>
            <p><strong>Area of Expertise:</strong> {{ $supervisor->area_of_expertise }}</p>
            <p><strong>Department:</strong> {{ $supervisor->department }}</p>
        </div>
    </div>
</div>
@endsection
