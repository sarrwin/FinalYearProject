@extends('layouts.app')

@section('content')
<div class="container">
   
<br>
<br>
<br>
<br>
<div class="row">
    <!-- Book Appointments -->
    <div class="col-md-4 mb-3">
    <div class="card shadow-sm h-100">
        <div class="card-body d-flex flex-column bg-[#D5C4F3]">
            <div class="mb-3 text-center">
                <i class="fas fa-calendar-alt fa-3x text-primary"></i>
            </div>
            <h5 class="card-title text-center">Book Appointments</h5>
            <p class="card-text text-center">Book an appointment with your preferred supervisor.</p>
            <a href="{{ route('supervisors.index') }}" onclick="handleViewDetailsClick(event)" class="btn btn-success mt-auto align-self-center">Book Appointment</a>
        </div>
    </div>
</div>


   <!-- Google Auth Modal -->
   <div class="modal fade" id="googleAuthModal" tabindex="-1" aria-labelledby="googleAuthModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="googleAuthModalLabel">Google Sign-In Required</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>To view the appointment details, please sign in to Google to sync with Google Calendar.</p>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('google.auth') }}" class="btn btn-primary" id="googleAuthButton">Sign in with Google</a>
                </div>
            </div>
        </div>
    </div>
   
    <!-- Upcoming Appointments -->
    <div class="col-md-4 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex flex-column">
                <div class="mb-3 text-center">
                    <i class="fas fa-clock fa-3x text-success"></i>
                </div>
                <h5 class="card-title text-center">Upcoming Appointments</h5>
                <p class="card-text text-center">Check your upcoming appointments.</p>
                <a href="{{ route('students.appointments.upcoming') }}" class="btn btn-warning mt-auto align-self-center">Upcoming Appointments</a>
            </div>
        </div>
    </div>

    <!-- Past Appointments -->
    <div class="col-md-4 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex flex-column">
                <div class="mb-3 text-center">
                    <i class="fas fa-history fa-3x text-warning"></i>
                </div>
                <h5 class="card-title text-center">Past Appointments</h5>
                <p class="card-text text-center">Review your past appointments.</p>
                <a href="{{ route('students.appointments.past') }}" class="btn btn-danger mt-auto align-self-center">Past Appointments</a>
            </div>
        </div>
    </div>
</div>


<script>
    // Check session flag for showing the Google Auth modal
    let googleAuthShown = {{ session('google_auth_shown') ? 'true' : 'false' }};
    console.log('Initial googleAuthShown:', googleAuthShown);

    


    function handleViewDetailsClick(event) {
        if (!googleAuthShown) {
            console.log('Google Auth modal will be shown.');
            event.preventDefault(); // Prevent navigation on first click
            googleAuthShown = true;

            // Update the session using an AJAX call
            fetch("{{ route('session.update') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ google_auth_shown: true })
            }).then(response => {
                if (response.ok) {
                    console.log('Session updated successfully.');
                } else {
                    console.error('Failed to update session.');
                }
            });

            // Show the Google Auth modal
            const modal = new bootstrap.Modal(document.getElementById('googleAuthModal'));
            modal.show();
        } else {
            console.log('Google Auth modal already shown, navigating directly.');
        }
    }



</script>









@endsection
