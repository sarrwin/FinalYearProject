@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="text-center my-4">Upcoming Appointments</h1>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    @if($appointments->isEmpty())
        <div class="alert alert-warning text-center">
            <strong>No upcoming appointments.</strong> Please check back later.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Supervisor</th>
                        <th>Project</th>
                        <th>Meeting Details</th>
                        <th>Gmeet</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($appointments as $appointment)
                        <tr data-id="{{ $appointment->id }}">
                        <td data-column="date">{{ \Carbon\Carbon::parse($appointment->date)->format('d M Y') }}</td>

                            <td data-column="time">
                        {{ \Carbon\Carbon::parse($appointment->start_time)->format('h:i A') }} -
                        {{ \Carbon\Carbon::parse($appointment->end_time)->format('h:i A') }}
                    </td>
                            <td>{{ $appointment->supervisor->name ?? 'N/A' }}</td>
                            <td>{{ $appointment->project->title ?? 'General' }}</td>
                            <td id="meeting-details-{{ $appointment->id }}">{{ $appointment->slot->meeting_details }}</td>
                            <td>
                                @if ($appointment->Gmeet_link)
                                    <a href="{{ $appointment->Gmeet_link }}" target="_blank" class="btn btn-primary btn-sm">Join Meeting</a>
                                @else
                                    <span class="text-muted">No link available</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $appointment->status === 'accepted' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($appointment->status) }}
                                </span>
                            </td>
                            <td>
                        <!-- Actions for editing and canceling appointments -->
                        <i class="fa fa-pencil text-primary" style="cursor: pointer;" onclick="showEditForm({{ $appointment->id }})"></i>
                        <i class="fa fa-trash-o text-danger mx-2" style="cursor: pointer;" onclick="showCancelModal({{ $appointment->id }})"></i>
                    </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-end mt-4">
            {{ $appointments->links('vendor.pagination.bootstrap-5') }}


        </div>
    @endif
</div>


<div class="modal fade" id="editAppointmentModal" tabindex="-1" aria-labelledby="editAppointmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <form id="edit-appointment-form" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="editAppointmentModalLabel">Edit Appointment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="meeting_details" class="form-label">Meeting Details</label>
                        <input type="text" name="meeting_details" id="modal-meeting-details" class="form-control" placeholder="Enter meeting details" required>
                    </div>
                    <div class="mb-3">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" name="date" id="modal-date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="start_time" class="form-label">Start Time</label>
                        <input type="time" name="start_time" id="modal-start-time" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="end_time" class="form-label">End Time</label>
                        <input type="time" name="end_time" id="modal-end-time" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function showEditForm(appointmentId) {
        const meetingDetails = document.getElementById(`meeting-details-${appointmentId}`).innerText.trim();
        const date = document.querySelector(`tr[data-id="${appointmentId}"] td[data-column="date"]`).innerText.trim();
        const timeRange = document.querySelector(`tr[data-id="${appointmentId}"] td[data-column="time"]`).innerText.trim().split(' - ');

        // Populate modal fields
        document.getElementById('modal-meeting-details').value = meetingDetails;
        document.getElementById('modal-date').value = formatDateForInput(date);
        document.getElementById('modal-start-time').value = formatTimeForInput(timeRange[0]);
        document.getElementById('modal-end-time').value = formatTimeForInput(timeRange[1]);

        // Set the form action dynamically
        document.getElementById('edit-appointment-form').action = `/students/appointments/${appointmentId}/updateMeetingDetails`;

        // Show the modal
        const modal = new bootstrap.Modal(document.getElementById('editAppointmentModal'));
        modal.show();
    }

    function showCancelModal(appointmentId) {
        document.getElementById('cancel-reason-form').action = `/appointments/cancel/${appointmentId}`;
        const modal = new bootstrap.Modal(document.getElementById('cancelReasonModal'));
        modal.show();
    }

    function formatDateForInput(dateString) {
        const [day, month, year] = dateString.split(' ');
        const monthMap = { Jan: '01', Feb: '02', Mar: '03', Apr: '04', May: '05', Jun: '06', Jul: '07', Aug: '08', Sep: '09', Oct: '10', Nov: '11', Dec: '12' };
        return `${year}-${monthMap[month]}-${day}`;
    }

    function formatTimeForInput(timeString) {
        const [time, meridian] = timeString.split(' ');
        let [hours, minutes] = time.split(':');
        if (meridian === 'PM' && hours !== '12') {
            hours = (parseInt(hours) + 12).toString();
        } else if (meridian === 'AM' && hours === '12') {
            hours = '00';
        }
        return `${hours.padStart(2, '0')}:${minutes}`;
    }
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const highlightedRow = document.querySelector('tr.table-primary');
        if (highlightedRow) {
            highlightedRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    
</script>







@endsection
