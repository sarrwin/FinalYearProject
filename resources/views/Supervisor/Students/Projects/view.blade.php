@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ $project->title  }}</h1>
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Project Details</h5>
            <p class="card-text"><strong>Description:</strong> {{ $project->description }}</p>
            <p class="card-text"><strong>Supervisor:</strong> {{ $project->supervisor->name }}</p>
            <p class="card-text"><strong>Assigned Students:</strong>
                @foreach ($project->students as $student)
                    {{ $student->name }}@if (!$loop->last), @endif
                @endforeach
            </p>
        </div>
    </div>
   
</div>
@endsection
