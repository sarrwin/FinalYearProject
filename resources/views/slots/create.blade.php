@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Create Slot</h1>
    <form action="{{ route('slots.store') }}" method="POST" id="slotForm" novalidate>
    @csrf
    <div class="mb-3">
        <label for="date" class="form-label">Date</label>
        <input type="date" name="date" id="date" class="form-control" value="{{ old('date') }}">
        <div class="invalid-feedback" id="dateError" style="display: none;">The date is required and must not be in the past.</div>
    </div>

    <div class="mb-3">
        <label for="start_time" class="form-label">Start Time</label>
        <input type="time" name="start_time" id="start_time" class="form-control" value="{{ old('start_time') }}">
        <div class="invalid-feedback" id="startTimeError" style="display: none;">Start time is required.</div>
    </div>

    <div class="mb-3">
        <label for="end_time" class="form-label">End Time</label>
        <input type="time" name="end_time" id="end_time" class="form-control" value="{{ old('end_time') }}">
        <div class="invalid-feedback" id="endTimeError" style="display: none;">End time is required and must be after the start time.</div>
    </div>

    <div class="mb-3">
        <label for="project_id" class="form-label">Project</label>
        <select name="project_id" id="project_id" class="form-control">
            <option value="">Select a project</option>
            @foreach($projects as $project)
                <option value="{{ $project->id }}">{{ $project->title }}</option>
            @endforeach
        </select>
        <div class="invalid-feedback" id="projectError" style="display: none;">Please select a project.</div>
    </div>

    <div class="mb-3">
    <label for="meeting_details" class="form-label">Meeting Details</label>
    <input 
        type="text" 
        name="meeting_details" 
        id="meeting_details" 
        class="form-control" 
        value="{{ old('meeting_details') }}" 
        maxlength="255"
    >
    <div class="invalid-feedback" id="meetingDetailsError" style="display: none;">Meeting details cannot exceed 255 characters.</div>
</div>

    <div class="mb-3">
    <label for="repeat_weeks" class="form-label">Repeat Weekly For (Weeks)</label>
    <input 
        type="number" 
        name="repeat_weeks" 
        id="repeat_weeks" 
        class="form-control" 
        value="{{ old('repeat_weeks') }}"
        min="1" 
        max="52"
    >
    <div class="invalid-feedback" id="repeatWeeksError" style="display: none;">Repeat weeks must be a number between 1 and 52, or leave it empty.</div>
</div>

    <button type="submit" class="btn btn-primary">Create Slot</button>

    
</form>

</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("slotForm");

    form.addEventListener("input", function (event) {
        validateField(event.target);
    });

    form.addEventListener("submit", function (event) {
        let isValid = validateForm();
        if (!isValid) {
            event.preventDefault(); // Prevent form submission if validation fails
        }
    });

    function validateForm() {
        let isValid = true;

        // Validate date
        const dateField = document.getElementById("date");
        const dateError = document.getElementById("dateError");
        if (!dateField.value || new Date(dateField.value) < new Date().setHours(0, 0, 0, 0)) {
            dateError.style.display = "block";
            dateField.classList.add("is-invalid");
            isValid = false;
        } else {
            dateError.style.display = "none";
            dateField.classList.remove("is-invalid");
        }

        // Validate start time
        const startTimeField = document.getElementById("start_time");
        const startTimeError = document.getElementById("startTimeError");
        if (!startTimeField.value) {
            startTimeError.style.display = "block";
            startTimeField.classList.add("is-invalid");
            isValid = false;
        } else {
            startTimeError.style.display = "none";
            startTimeField.classList.remove("is-invalid");
        }

        // Validate end time
        const endTimeField = document.getElementById("end_time");
        const endTimeError = document.getElementById("endTimeError");
        const startTime = startTimeField.value;
        if (!endTimeField.value || (startTime && endTimeField.value <= startTime)) {
            endTimeError.style.display = "block";
            endTimeField.classList.add("is-invalid");
            isValid = false;
        } else {
            endTimeError.style.display = "none";
            endTimeField.classList.remove("is-invalid");
        }

        // Validate project
        const projectField = document.getElementById("project_id");
        const projectError = document.getElementById("projectError");
        if (!projectField.value) {
            projectError.style.display = "block";
            projectField.classList.add("is-invalid");
            isValid = false;
        } else {
            projectError.style.display = "none";
            projectField.classList.remove("is-invalid");
        }

        return isValid;


        // Validate repeat weeks
    const repeatWeeksField = document.getElementById("repeat_weeks");
    const repeatWeeksError = document.getElementById("repeatWeeksError");
    if (repeatWeeksField.value && (repeatWeeksField.value < 1 || repeatWeeksField.value > 52)) {
        repeatWeeksError.style.display = "block";
        repeatWeeksField.classList.add("is-invalid");
        isValid = false;
    } else {
        repeatWeeksError.style.display = "none";
        repeatWeeksField.classList.remove("is-invalid");
    }

    return isValid;


    const meetingDetailsField = document.getElementById("meeting_details");
    const meetingDetailsError = document.getElementById("meetingDetailsError");
    if (meetingDetailsField.value && meetingDetailsField.value.length > 255) {
        meetingDetailsError.style.display = "block";
        meetingDetailsField.classList.add("is-invalid");
        isValid = false;
    } else {
        meetingDetailsError.style.display = "none";
        meetingDetailsField.classList.remove("is-invalid");
    }

    return isValid;
}

    

    function validateField(field) {
        if (field.id === "date") {
            const dateError = document.getElementById("dateError");
            if (!field.value || new Date(field.value) < new Date().setHours(0, 0, 0, 0)) {
                dateError.style.display = "block";
                field.classList.add("is-invalid");
            } else {
                dateError.style.display = "none";
                field.classList.remove("is-invalid");
            }
        }

        if (field.id === "start_time") {
            const startTimeError = document.getElementById("startTimeError");
            if (!field.value) {
                startTimeError.style.display = "block";
                field.classList.add("is-invalid");
            } else {
                startTimeError.style.display = "none";
                field.classList.remove("is-invalid");
            }
        }

        if (field.id === "end_time") {
            const endTimeError = document.getElementById("endTimeError");
            const startTime = document.getElementById("start_time").value;
            if (!field.value || (startTime && field.value <= startTime)) {
                endTimeError.style.display = "block";
                field.classList.add("is-invalid");
            } else {
                endTimeError.style.display = "none";
                field.classList.remove("is-invalid");
            }
        }

        if (field.id === "project_id") {
            const projectError = document.getElementById("projectError");
            if (!field.value) {
                projectError.style.display = "block";
                field.classList.add("is-invalid");
            } else {
                projectError.style.display = "none";
                field.classList.remove("is-invalid");
            }
        }

        if (field.id === "repeat_weeks") {
        const repeatWeeksError = document.getElementById("repeatWeeksError");
        if (field.value && (field.value < 1 || field.value > 52)) {
            repeatWeeksError.style.display = "block";
            field.classList.add("is-invalid");
        } else {
            repeatWeeksError.style.display = "none";
            field.classList.remove("is-invalid");
        }
    }

    if (field.id === "meeting_details") {
        const meetingDetailsError = document.getElementById("meetingDetailsError");
        if (field.value && field.value.length > 255) {
            meetingDetailsError.style.display = "block";
            field.classList.add("is-invalid");
        } else {
            meetingDetailsError.style.display = "none";
            field.classList.remove("is-invalid");
        }
    }
}
    
});
</script>
@endsection
