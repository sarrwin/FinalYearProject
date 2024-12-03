@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Supervisor Profile -->
    <div class="row my-5">
        <div class="col-lg-4">
            <div class="card shadow border-0">
                <div class="card-body text-center">
                    <img src="{{ asset('uploads/' . $supervisor->profile_picture) }}" alt="Profile Picture" class="img-fluid rounded-circle mb-3" style="width: 120px; height: 120px;">
                    <h4 class="card-title">{{ $supervisor->name }}</h4>
                    <p class="text-muted mb-1">{{ $supervisor->department }}</p>
                    <p class="text-muted mb-2">{{ $supervisor->area_of_expertise }}</p>
                    <p><strong>Contact:</strong> {{ $supervisor->contact_number }}</p>
                    <p><strong>Email:</strong> <a href="mailto:{{ $supervisor->email }}">{{ $supervisor->email }}</a></p>
                    <a href="{{ route('appointments.slots', $supervisor->id) }}" class="btn btn-primary w-100 mt-3">Book Appointment</a>
                </div>
            </div>
        </div>

        <!-- Supervisor Details -->
        <div class="col-lg-8">
            <div class="card shadow border-0">
                <div class="card-body">
                    <h4 class="card-title mb-4">Supervisor's Details</h4>
                    <p><strong>Office Address:</strong> {{ $supervisor->office_address }}</p>
                    <p><strong>Area of Expertise:</strong> {{ $supervisor->area_of_expertise }}</p>
                    <p><strong>Department:</strong> {{ $supervisor->department }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Supervisor's Projects -->
    <div class="row my-5">
        <div class="col-lg-12">
            <div class="card shadow border-0">
                <div class="card-body">
                    <h4 class="card-title mb-4">Projects Supervised</h4>

                    <!-- Filter by Session -->
                    <form method="GET" action="{{ route('supervisor.profile.show', $supervisor->id) }}" class="d-flex align-items-center mb-4">
                        <label for="session" class="form-label me-3 mb-0">Filter by Session:</label>
                        <select name="session" id="session" class="form-select me-3" style="width: 250px;">
                            <option value="">All Sessions</option>
                            @foreach($sessions as $session)
                                <option value="{{ $session }}" {{ request('session') == $session ? 'selected' : '' }}>
                                    {{ $session }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </form>

                    <!-- Projects Table -->
                    @if($projects->isEmpty())
                        <p class="text-muted">No projects found for the selected session.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Project Title</th>
                                        <th>Description</th>
                                        <th>Students</th>
                                        <th>Session</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($projects as $project)
                                        <tr>
                                            <td>
                                                <strong>{{ $project->title }}</strong>
                                            </td>
                                            <td>{{ $project->description }}</td>
                                            <td>
                                                @foreach($project->students as $student)
                                                    <span class="badge bg-secondary">{{ $student->name }}</span>
                                                @endforeach
                                            </td>
                                            <td>{{ $project->session }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
