@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h3>Student Profile</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <!-- Display Profile Picture -->
                    <div class="profile-picture mb-3">
                        <img src="{{ $student->profile_picture ? asset('uploads/' . $student->profile_picture) : asset('images/default-profile.png') }}" 
                             alt="Profile Picture" 
                             class="img-fluid rounded-circle" 
                             style="width: 150px; height: 150px;">
                    </div>
                </div>
                <div class="col-md-8">
                    <!-- Display Student Details -->
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <th>Name:</th>
                                <td>{{ $student->name }}</td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td>{{ $student->email }}</td>
                            </tr>
                            <tr>
                                <th>Matric Number:</th>
                                <td>{{ $student->matric_number }}</td>
                            </tr>
                            <tr>
                                <th>Contact Number:</th>
                                <td>{{ $student->contact_number ?? 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <th>Department:</th>
                                <td>{{ $student->department ?? 'Not assigned' }}</td>
                            </tr>
                            <tr>
                                <th>Current Project:</th>
                                <td>
                                    {{ $student->projects->first()->title ?? 'No current project assigned' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
