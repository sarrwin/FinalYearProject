@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="text-center my-4">User Management</h1>

    <!-- Success Message -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.index') }}" method="GET" class="d-flex align-items-center">
                <label for="role" class="me-2">Filter by Role:</label>
                <select name="role" id="role" class="form-select me-3" style="width: 200px;" onchange="this.form.submit()">
                    <option value="">All Roles</option>
                    <option value="student" {{ request('role') === 'student' ? 'selected' : '' }}>Student</option>
                    <option value="supervisor" {{ request('role') === 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                    <option value="coordinator" {{ request('role') === 'coordinator' ? 'selected' : '' }}>Coordinator</option>
                </select>
                <button type="submit" class="btn btn-outline-primary">Apply</button>
            </form>
        </div>
    </div>

    <!-- User Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>ID</th>
                    <th>Role</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if($user->role === 'supervisor')
                                {{ $user->supervisor->staff_id }}
                            @else
                                {{optional( $user->student)->matric_number ?? 'staff_id'}}
                            @endif
                        </td>
                        <td>{{ ucfirst($user->role) }}</td>
                        <td>
                            @if($user->role === 'supervisor' && $user->supervisor)
                                @if(!$user->supervisor->is_coordinator)
                                    <form action="{{ route('admin.verify.coordinator', $user->supervisor->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">Verify as Coordinator</button>
                                    </form>
                                @else
                                    <span class="badge bg-success">Verified</span>
                                    <form action="{{ route('admin.demote.coordinator', $user->supervisor->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-sm">Demote to Supervisor</button>
                                    </form>
                                @endif
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
