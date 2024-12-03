@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="text-center my-4">Create Project</h1>

    <!-- Warning Alert -->
    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Project Creation Form -->
    <div class="card shadow-sm p-4">
        <form action="{{ route('supervisor.projects.store') }}" method="POST">
            @csrf

            <!-- Title -->
            <div class="mb-4">
                <label for="title" class="form-label fw-bold">Title</label>
                <input type="text" name="title" id="title" class="form-control" placeholder="Enter project title" required>
            </div>

            <!-- Description -->
            <div class="mb-4">
                <label for="description" class="form-label fw-bold">Description</label>
                <textarea name="description" id="description" class="form-control" rows="4" placeholder="Provide a brief project description" required></textarea>
            </div>

            <!-- Number of Students Required -->
            <div class="mb-4">
                <label for="students_required" class="form-label fw-bold">Number of Students Required</label>
                <input type="number" name="students_required" id="students_required" class="form-control" min="1" required>
            </div>

            <!-- Filter Students -->
            <div class="mb-4">
                <label class="form-label fw-bold">Filter Students</label>
                <div class="row">
                    <div class="col-md-6">
                        <input type="text" id="studentSearch" class="form-control" placeholder="Search by name">
                    </div>
                    <div class="col-md-6">
                        <select id="departmentFilter" class="form-select">
                            <option value="">Filter by Department</option>
                            <option value="Artificial Intelligence">Artificial Intelligence</option>
                            <option value="Software Engineering">Software Engineering</option>
                            <option value="Computer System and Network">Computer System and Network</option>
                            <option value="Multimedia">Multimedia</option>
                            <option value="Information System">Information System</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Assign Students -->
            <div class="mb-4">
                <label for="students" class="form-label fw-bold">Assign Students</label>
                <select name="students[]" id="students" class="form-select" multiple>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}" data-department="{{ $student->department }}">
                            {{ $student->name }} ({{ $student->department }})
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Hold <kbd>Ctrl</kbd> (Windows) or <kbd>Command</kbd> (Mac) to select multiple students.</small>
            </div>

            <!-- Session -->
            <div class="mb-4">
                <label for="session" class="form-label fw-bold">Session</label>
                <select name="session" id="session" class="form-select" required>
                    <option value="">Select Session</option>
                    <option value="SEMESTER 1 2023/2024">SEMESTER 1 2023/2024</option>
                    <option value="SEMESTER 2 2023/2024">SEMESTER 2 2023/2024</option>
                </select>
            </div>

            <!-- Department -->
            <div class="mb-4">
                <label for="department" class="form-label fw-bold">Department</label>
                <select name="department" id="department" class="form-select" required>
                    <option value="">Select Department</option>
                    <option value="Artificial Intelligence">Artificial Intelligence</option>
                    <option value="Software Engineering">Software Engineering</option>
                    <option value="Computer System and Network">Computer System and Network</option>
                    <option value="Multimedia">Multimedia</option>
                    <option value="Information System">Information System</option>
                </select>
            </div>

            <!-- Tools -->
            <div class="mb-4">
                <label for="tools" class="form-label fw-bold">Tools</label>
                <input type="text" name="tools" id="tools" class="form-control" value="{{ old('tools') }}" placeholder="Enter tools, separated by commas" required>
            </div>

            <!-- Submit Button -->
            <div class="text-center">
                <button type="submit" class="btn btn-primary btn-lg">Create Project</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const studentsSelect = document.getElementById('students');
        const studentsRequiredInput = document.getElementById('students_required');
        const studentSearch = document.getElementById('studentSearch');
        const departmentFilter = document.getElementById('departmentFilter');

        studentSearch.addEventListener('input', filterStudents);
        departmentFilter.addEventListener('change', filterStudents);

        function filterStudents() {
            const searchValue = studentSearch.value.toLowerCase();
            const departmentValue = departmentFilter.value;
            const options = studentsSelect.options;

            Array.from(options).forEach(option => {
                const matchesName = option.text.toLowerCase().includes(searchValue);
                const matchesDepartment = !departmentValue || option.getAttribute('data-department') === departmentValue;

                option.style.display = matchesName && matchesDepartment ? '' : 'none';
            });
        }

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
