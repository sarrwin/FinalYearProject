@extends('layouts.app')

@section('content')

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="container">
    <h1>Available Slots for {{ $supervisor->name }}</h1>
    
    <!-- Slots Section -->
    <div class="row">
        <div class="col-md-12">
            <h2>Available Slots</h2>
            <table class="table table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>Date</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Meeting Details</th>
                        <th>Project</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($slots as $slot)
                        @if ($slot->date >= \Carbon\Carbon::now()->toDateString())
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($slot->date)->format('d M Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($slot->start_time)->format('h:i A') }}</td>
                                <td>{{ \Carbon\Carbon::parse($slot->end_time)->format('h:i A') }}</td>
                                <td>{{ $slot->meeting_details }}</td>
                                <td>{{ $slot->project->title ?? 'General' }}</td>
                                <td>
                                @if ($slot->booked === null)
                    <span class="badge bg-warning">Pending Confirmation</span>
                @elseif ($slot->booked)
                    <span class="badge bg-danger">Booked</span>
                @else
                    <span class="badge bg-success">Available</span>
                @endif
                                </td>
                                <td>
                                @if ($slot->booked === null || $slot->booked) 
                    {{-- Disable button if booked is null (pending) or true (booked) --}}
                    <button class="btn btn-sm btn-secondary" disabled>Unavailable</button>
                @else
                    {{-- Enable button if booked is false (available) --}}
                    <form action="{{ route('appointments.bookSlot', $slot->id) }}" method="POST" onsubmit="submitBookingForm(event)">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success">Book</button>
                    </form>
                @endif
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No slots available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Request Custom Time Section -->
    <div class="row mt-5">
        <div class="col-md-12">
            <h2>Request Custom Time</h2>
            <p class="text-muted">Can't find a suitable slot? Request a custom appointment time.</p>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#requestCustomTimeModal">
                Request Here
            </button>
        </div>
    </div>
</div>

<!-- Modal for Custom Time Request -->
<div class="modal fade" id="requestCustomTimeModal" tabindex="-1" aria-labelledby="requestCustomTimeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="requestCustomTimeModalLabel">Request Custom Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('appointments.requestOwnTime') }}" method="POST">
                    @csrf
                    <div class="row">
                        <!-- Date -->
                        <div class="col-md-6 mb-3">
                            <label for="date" class="form-label">Date</label>
                            <input type="date" name="date" id="date" class="form-control" required>
                        </div>
                        <!-- Start Time -->
                        <div class="col-md-6 mb-3">
                            <label for="start_time" class="form-label">Start Time</label>
                            <input type="time" name="start_time" id="start_time" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <!-- End Time -->
                        <div class="col-md-6 mb-3">
                            <label for="end_time" class="form-label">End Time</label>
                            <input type="time" name="end_time" id="end_time" class="form-control" required>
                        </div>
                        <!-- Meeting Type -->
                        <div class="col-md-6 mb-3">
                            <label for="is_project_meeting" class="form-label">Meeting Type</label>
                            <select name="is_project_meeting" id="is_project_meeting" class="form-control" onchange="toggleProjectField()" required>
                                <option value="0">General Meeting</option>
                                <option value="1">Project Meeting</option>
                            </select>
                        </div>
                    </div>

                    <!-- Project Selection -->
                    <div class="mb-3 d-none" id="projectField">
                        <label for="project_id" class="form-label">Select Project</label>
                        @if ($projects->isNotEmpty())
                            <select name="project_id" id="project_id" class="form-control">
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->title }}</option>
                                @endforeach
                            </select>
                        @else
                            <p class="text-muted">No projects available.</p>
                        @endif
                    </div>

                    <!-- Reason -->
                    <div class="mb-3">
                        <label for="request_reason" class="form-label">Reason</label>
                        <textarea name="request_reason" id="request_reason" class="form-control" rows="3" placeholder="Briefly explain the purpose of this meeting." required></textarea>
                    </div>

                    <input type="hidden" name="supervisor_id" value="{{ $supervisor->id }}">
                    
                    <!-- Submit Button -->
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Submit Request</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 1050; align-items: center; justify-content: center;">
    <div class="text-center">
        <div class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <h4 class="text-light mt-3">Your appointment is booking...</h4>
    </div>
</div>

<script>
    function showLoadingOverlay() {
        document.getElementById('loadingOverlay').style.display = 'flex';
    }

    function submitBookingForm(event) {
        event.preventDefault();
        showLoadingOverlay();
        event.target.submit();
    }

    function toggleProjectField() {
        const isProjectMeeting = document.getElementById('is_project_meeting').value;
        const projectField = document.getElementById('projectField');
        if (isProjectMeeting === '1') {
            projectField.classList.remove('d-none');
        } else {
            projectField.classList.add('d-none');
        }
    }
</script>
@endsection
