@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Logbook Entry</h1>
    <form action="{{ route('students.logbook.update', $logbookEntry->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="activity" class="form-label">Activity</label>
            <textarea name="activity" id="activity" class="form-control" required>{{ $logbookEntry->activity }}</textarea>
        </div>
        <div class="mb-3">
            <label for="activity_date" class="form-label">Activity Date</label>
            <input type="date" name="activity_date" id="activity_date" class="form-control" value="{{ $logbookEntry->activity_date }}" required>
        </div>
        <div class="mb-3">
            <label for="reference_file" class="form-label">Upload Reference Document</label>
            <input type="file" name="reference_file" id="reference_file" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg">
        </div>
       
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
