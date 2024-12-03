@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Create Logbook Entry</h1>
    <form action="{{ route('students.logbook.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
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
       
        <button type="submit" class="btn btn-primary">Save</button>
    </form>
</div>
@endsection
