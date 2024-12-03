@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Students</h1>
    @dd($students) <!-- Debugging line to check data -->
    <ul class="list-group mt-4">
        @foreach ($students as $student)
            <li class="list-group-item">
                <a href="{{ route('student.profile.show', $student->id) }}">{{ $student->name }}</a>
            </li>
        @endforeach
    </ul>
</div>
@endsection
