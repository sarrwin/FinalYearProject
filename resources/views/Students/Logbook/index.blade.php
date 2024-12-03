@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<div class="container">
    <h1 class="mb-4">Project Logbooks</h1>

    <!-- Button to trigger the create entry modal -->
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createLogbookModal">
        <i class="bi bi-plus-circle"></i> Create New Entry
    </button>

    <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Student</th>
                <th>Project</th>
                <th>Activity</th>
                <th>Date</th>
                <th>Verified</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($logbookEntries as $entry)
                <tr>
                    <td>{{ optional($entry->student)->name ?? 'N/A' }}</td>
                    <td>{{ optional($entry->logbook->project)->title ?? 'No project' }}</td>
                    <td class="activity-cell">
                        <div class="activity-text">
                            {{ \Illuminate\Support\Str::limit($entry->activity, 50) }}
                            @if (strlen($entry->activity) > 50)
                                <a href="javascript:void(0);" class="see-more" data-full-text="{{ $entry->activity }}">See More</a>
                            @endif
                        </div>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($entry->activity_date)->format('Y-m-d') }}</td>
                    <td>{{ $entry->verified ? 'Yes' : 'No' }}</td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-2">
                            @if ($entry->logbookFiles)
                                @foreach ($entry->logbookFiles as $file)
                                    <a href="{{ Storage::url($file->file_path) }}" class="btn btn-outline-info btn-sm" target="_blank" title="View File">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                @endforeach
                            @endif

                            @if ($entry->student_id === Auth::id() && !$entry->verified)
                            <button class="btn btn-outline-warning btn-sm edit-logbook" data-id="{{ $entry->id }}" data-activity="{{ $entry->activity }}" data-date="{{ $entry->activity_date }}">
                                    <i class="fa fa-pencil text-primary"></i>
                                
                                <form action="{{ route('students.logbook.destroy', $entry->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Modal for creating a new logbook entry -->
<div class="modal fade" id="createLogbookModal" tabindex="-1" aria-labelledby="createLogbookModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createLogbookModalLabel">Create Logbook Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('students.logbook.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="project_id" class="form-label">Project</label>
                        <select name="project_id" id="project_id" class="form-select" required>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="activity" class="form-label">Activity</label>
                        <textarea name="activity" id="activity" class="form-control" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="activity_date" class="form-label">Activity Date</label>
                        <input type="date" name="activity_date" id="activity_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="reference_file" class="form-label">Upload Reference Document</label>
                        <input type="file" name="reference_file" id="reference_file" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal for Editing Logbook Entry -->
<div class="modal fade" id="editLogbookModal" tabindex="-1" aria-labelledby="editLogbookModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editLogbookModalLabel">Edit Logbook Entry</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editLogbookForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editActivity" class="form-label">Activity</label>
                        <textarea name="activity" id="editActivity" class="form-control" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editActivityDate" class="form-label">Activity Date</label>
                        <input type="date" name="activity_date" id="editActivityDate" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="editReferenceFile" class="form-label">Upload Reference Document</label>
                        <input type="file" name="reference_file" id="editReferenceFile" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>





<div class="modal fade" id="activityModal" tabindex="-1" aria-labelledby="activityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="activityModalLabel">Activity Details</h5>
                <!-- Close button with Bootstrap 4 compatibility -->
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="fullActivityText"></p>
            </div>
            <div class="modal-footer">
                <!-- Close button with Bootstrap 4 compatibility -->
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .activity-cell {
        max-width: 300px; /* Limit the width of the activity column */
        white-space: nowrap; /* Prevent wrapping */
        overflow: hidden; /* Hide overflow */
        text-overflow: ellipsis; /* Add ellipsis for overflowing text */
    }
    .see-more {
        color: #007bff;
        cursor: pointer;
        text-decoration: underline;
    }
</style>
@endsection
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
 document.addEventListener('DOMContentLoaded', function () {
    console.log(bootstrap.Tooltip.VERSION);

        // Handle "See More" click
        document.querySelectorAll('.see-more').forEach(function (button) {
            button.addEventListener('click', function () {
                // Get the full activity text from the data attribute
                const fullText = this.getAttribute('data-full-text');

                // Set the full text in the modal body
                const modalBody = document.getElementById('fullActivityText');
                modalBody.textContent = fullText;

                // Show the modal
                const activityModal = new bootstrap.Modal(document.getElementById('activityModal'));
                activityModal.show();
            });
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Handle Edit button click
        document.querySelectorAll('.edit-logbook').forEach(function (button) {
            button.addEventListener('click', function () {
                const entryId = this.getAttribute('data-id');
                const activity = this.getAttribute('data-activity');
                const activityDate = this.getAttribute('data-date');

                // Populate the modal form with the logbook data
                document.getElementById('editActivity').value = activity;
                document.getElementById('editActivityDate').value = activityDate;

                // Update the form's action URL
                const form = document.getElementById('editLogbookForm');
                form.action = `/students/logbook/${entryId}`;

                // Show the modal
                $('#editLogbookModal').modal('show');
            });
        });
    });
</script>

<!-- Bootstrap JS -->


