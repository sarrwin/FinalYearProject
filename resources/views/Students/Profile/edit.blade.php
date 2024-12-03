@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4 text-center">Edit Profile</h1>
    
    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="row">
    <div class="col-md-4">
    {{-- Profile Picture --}}
    <div class="card shadow-lg mb-4">
        <div class="card-body text-center">
            {{-- Profile Picture --}}
            <div class="d-flex justify-content-center">
                <img src="{{ $user->profile_picture ? asset('uploads/' . $user->profile_picture) : asset('profile-placeholder.png') }}" 
                     alt="Profile Picture" 
                     class="img-fluid rounded-circle mb-3 shadow-sm" 
                     style="width: 150px; height: 150px;">
            </div>
            
            {{-- User Details --}}
            <h5 class="mt-3">{{ $user->name }}</h5>
            <p class="text-muted mb-1"><strong>Matric Number:</strong> {{ $user->student->matric_number }}</p>
            <p class="text-muted"><strong>Email:</strong> {{ $user->email }}</p>
        </div>
    </div>
</div>

        <div class="col-md-8">
            {{-- Edit Form --}}
            <div class="card shadow-lg">
                <div class="card-body">
                <form action="{{ route('students.profile.update', $user->id) }}" method="POST" enctype="multipart/form-data">
    @csrf

                        {{-- Profile Picture --}}
                        <div class="mb-3">
                            <label for="profile_picture" class="form-label">Profile Picture</label>
                            <input type="file" name="profile_picture" id="profile_picture" class="form-control">
                        </div>

                        {{-- Contact Number --}}
                        <div class="mb-3">
                            <label for="contact_number" class="form-label">Contact Number</label>
                            <input type="text" name="contact_number" id="contact_number" class="form-control" 
                                   value="{{ old('contact_number', $user->contact_number) }}">
                        </div>

                        {{-- Department --}}
                        <div class="mb-3">
                            <label for="department" class="form-label">Department</label>
                            <select name="department" id="department" class="form-select">
                                <option value="" disabled>Select Department</option>
                                @foreach(['Artificial Intelligence', 'Software Engineering', 'Computer System and Network', 'Multimedia', 'Information System'] as $department)
                                    <option value="{{ $department }}" {{ old('department', $user->department) == $department ? 'selected' : '' }}>
                                        {{ $department }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Save Button --}}
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
