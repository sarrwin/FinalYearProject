@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="row">
        <!-- Supervisor Profile Card -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-lg border-0">
                <div class="card-body text-center">
                    <img src="{{ $supervisor->profile_picture ? asset('uploads/' . $supervisor->profile_picture) : asset('profile-placeholder.png') }}" 
                         alt="Profile Picture" 
                         class="img-fluid rounded-circle mb-3 shadow-sm" 
                         style="width: 150px; height: 150px;">
                    <h4 class="card-title fw-bold">{{ $supervisor->name }}</h4>
                    <p class="text-muted mb-1"><strong>Email:</strong> {{ $supervisor->email }}</p>
                </div>
            </div>
        </div>

        <!-- Edit Profile Form -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-lg border-0">
                <div class="card-body">
                    <h4 class="card-title fw-bold mb-4">Edit Profile</h4>
                    <form action="{{ route('supervisor.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Profile Picture -->
                        <div class="mb-3">
                            <label for="profile_picture" class="form-label fw-semibold">Profile Picture</label>
                            <input type="file" name="profile_picture" id="profile_picture" class="form-control">
                        </div>

                        <!-- Contact Number -->
                        <div class="mb-3">
                            <label for="contact_number" class="form-label fw-semibold">Contact Number</label>
                            <input type="text" name="contact_number" id="contact_number" class="form-control" 
                                   value="{{ old('contact_number', $supervisor->contact_number) }}">
                        </div>

                        <!-- Office Address -->
                        <div class="mb-3">
                            <label for="office_address" class="form-label fw-semibold">Office Address</label>
                            <input type="text" name="office_address" id="office_address" class="form-control" 
                                   value="{{ old('office_address', $supervisor->office_address) }}">
                        </div>

                        <!-- Department -->
                        <div class="mb-3">
                            <label for="department" class="form-label fw-semibold">Department</label>
                            <select name="department" id="department" class="form-select">
                                <option value="" disabled>Select Department</option>
                                @foreach(['Artificial Intelligence', 'Software Engineering', 'Computer System and Network', 'Multimedia', 'Information System'] as $department)
                                    <option value="{{ $department }}" {{ old('department', $supervisor->department) == $department ? 'selected' : '' }}>
                                        {{ $department }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Programming Languages -->
                        <div class="mb-3">
                            <label for="programming_languages" class="form-label fw-semibold">Programming Languages</label>
                            <input id="programming_languages" name="programming_languages" class="form-control" 
                                   value="{{ old('programming_languages', $supervisor->programming_languages) }}">
                        </div>

                        <!-- Save Button -->
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary px-4">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects Section -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-lg border-0">
                <div class="card-body">
                    <h4 class="card-title fw-bold mb-4">Projects</h4>

                    <!-- Filter by Session -->
                    <form method="GET" action="{{ route('supervisor.profile.edit') }}" class="d-flex align-items-center mb-4">
                        <label for="session" class="form-label fw-semibold me-3 mb-0">Filter by Session:</label>
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
                            <table class="table table-bordered table-hover align-middle">
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
                                            <td><strong>{{ $project->title }}</strong></td>
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

<!-- Tagify Script -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var input = document.querySelector('#programming_languages');
        new Tagify(input, {
            whitelist: ["HTML", "Java", "Python", "JavaScript", "CSS", "C#", "C++", "Ruby", "PHP", "Swift"],
            dropdown: {
                maxItems: 10,
                classname: "tags-look",
                enabled: 0,
                closeOnSelect: false
            }
        });
    });
</script>
@endsection
