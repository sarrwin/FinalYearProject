@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Department's Projects</h1>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Supervisor</th>
                <th>Projects</th>
                <th>Department</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($projects as $project)
            <tr>
                <td>{{ $project->supervisor->name ?? 'No Supervisor' }}</td>
                <td>{{ $project->title }}</td>
                <td>{{ $project->department }}</td>
                <td>
                    <!-- Button to trigger modal -->
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#projectDetailsModal-{{ $project->id }}">
                        View Project
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

  
    </div>
   
</div>
@endsection
