@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Project</h1>
    @if (session('warning'))
        <div class="alert alert-warning">
            {{ session('warning') }}
        </div>
    @endif
    <form action="{{ route('supervisor.projects.update', $project->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <!-- Title Field -->
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" name="title" id="title" class="form-control" value="{{ $project->title }}" required>
        </div>

        <!-- Description Field -->
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" class="form-control" required>{{ $project->description }}</textarea>
        </div>

        <!-- Students Required Field -->
        <div class="mb-3">
            <label for="students_required" class="form-label">Number of Students Required</label>
            <input type="number" name="students_required" id="students_required" class="form-control" min="1" value="{{ $project->students_required }}" required>
        </div>

        <!-- Assign Students Field -->
        <div class="mb-3">
            <label for="students" class="form-label">Assign Students</label>
            <select name="students[]" id="students" class="form-control" multiple>
                @foreach ($students as $student)
                    <option value="{{ $student->id }}" {{ $project->students->contains($student->id) ? 'selected' : '' }}>{{ $student->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Session Field -->
        <div class="mb-3">
            <label for="session" class="form-label">Session</label>
            <select name="session" id="session" class="form-control">
                <option value="SEMESTER 1 2023/2024" {{ $project->session == 'SEMESTER 1 2023/2024' ? 'selected' : '' }}>SEMESTER 1 2023/2024</option>
                <option value="SEMESTER 2 2023/2024" {{ $project->session == 'SEMESTER 2 2023/2024' ? 'selected' : '' }}>SEMESTER 2 2023/2024</option>
                <!-- Add more options as needed -->
            </select>
        </div>

        <!-- Department Field -->
        <div class="mb-3">
            <label for="department" class="form-label">Department</label>
            <select name="department" id="department" class="form-control">
                <option value="Artificial Intelligence" {{ $project->department == 'Artificial Intelligence' ? 'selected' : '' }}>Artificial Intelligence</option>
                <option value="Software Engineering" {{ $project->department == 'Software Engineering' ? 'selected' : '' }}>Software Engineering</option>
                <option value="Computer System and Network" {{ $project->department == 'Computer System and Network' ? 'selected' : '' }}>Computer System and Network</option>
                <option value="Multimedia" {{ $project->department == 'Multimedia' ? 'selected' : '' }}>Multimedia</option>
                <option value="Information System" {{ $project->department == 'Information System' ? 'selected' : '' }}>Information System</option>
                <!-- Add more options as needed -->
            </select>
        </div>

        <!-- Tools Field -->
        <div class="mb-3">
            <label for="tools" class="form-label">Tools</label>
            <input type="text" name="tools" id="tools" class="form-control" value="{{ $project->tools }}" required>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary">Update Project</button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const studentsSelect = document.getElementById('students');
        const studentsRequiredInput = document.getElementById('students_required');

        studentsRequiredInput.addEventListener('input', function () {
            updateMaxSelections();
        });

        studentsSelect.addEventListener('change', function () {
            updateMaxSelections();
        });

        function updateMaxSelections() {
            const maxSelections = parseInt(studentsRequiredInput.value, 10) || 1;
            const selectedOptions = Array.from(studentsSelect.selectedOptions);

            if (selectedOptions.length > maxSelections) {
                selectedOptions.slice(maxSelections).forEach(option => option.selected = false);
                alert(`You can select up to ${maxSelections} students.`);
            }
        }
    });
</script>
@endsection
