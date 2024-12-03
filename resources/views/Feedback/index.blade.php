@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="text-center my-4">Feedback Management</h1>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>User</th>
                <th>Subject</th>
                <th>Message</th>
                <th>Screenshot</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($feedbacks as $feedback)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $feedback->user->name ?? 'Guest' }}</td>
                    <td>{{ $feedback->subject }}</td>
                    <td>{{ $feedback->message }}</td>
                    <td>
                        @if($feedback->screenshot)
                            <a href="{{ asset('storage/' . $feedback->screenshot) }}" target="_blank" class="btn btn-sm btn-info">View Screenshot</a>
                        @else
                            <span class="text-muted">No screenshot</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-{{ $feedback->status === 'resolved' ? 'success' : 'warning' }}">
                            {{ ucfirst($feedback->status) }}
                        </span>
                    </td>
                    <td>
                        @if($feedback->status === 'pending')
                            <form action="{{ route('feedback.resolve', $feedback->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">Mark as Resolved</button>
                            </form>
                        @else
                            <span class="text-muted">Resolved</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No feedback available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
