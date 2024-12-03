@extends('layouts.app')

@section('content')
<div class="container">


    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($project)

      <!-- Tabs Navigation -->
      <ul class="nav nav-tabs" id="studentTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="project-details-tab" data-bs-toggle="tab" data-bs-target="#project-details" type="button" role="tab" aria-controls="project-details" aria-selected="true">
                    Project Details & Tasks
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="leaderboard-tab" data-bs-toggle="tab" data-bs-target="#leaderboard" type="button" role="tab" aria-controls="leaderboard" aria-selected="false">
                    Leaderboard
                </button>
            </li>
        </ul>

         <!-- Tabs Content -->
         <div class="tab-content mt-3" id="studentTabsContent">
            <!-- Project Details & Tasks Tab -->
            <div class="tab-pane fade show active" id="project-details" role="tabpanel" aria-labelledby="project-details-tab">
    <div class="row">
    <!-- Project Information -->
    <div class="col-md-6 col-12 mb-3">
        <div class="card project-section">
            <div class="card-body">
                <h5 class="card-title">{{ $project->title }}</h5>
                <p><strong>Supervisor:</strong> {{ $project->supervisor->name ?? 'Not assigned' }}</p>
                <p><strong>Assigned Students:</strong> 
                    {{ $project->students->isNotEmpty() ? $project->students->pluck('name')->join(', ') : 'No Students Assigned' }}
                </p>
                <a href="{{ route('students.projects.details', $project->id) }}" class="btn btn-primary btn-sm">View Details</a>
            </div>
        </div>
    </div>

    <!-- Task Completion Progress -->
    <div class="col-md-3 col-12 mb-3">
        <div class="card task-completion-section2">
            <div class="card-body bg-[#D5C4F3]">
                <h5 class="card-title">Task Completion Progress</h5>
                <div class="progress mb-2">
                    <div class="progress-bar" role="progressbar" style="width: 0%;" id="taskProgressBar">0%</div>
                </div>
                <p id="progressText">Calculating progress...</p>
            </div>
        </div>
    </div>

    <!-- File Submission -->
    <div class="col-md-3 col-12 mb-3">
    <div class="card file-submission-section bg-[#D5C4F3]">
        <div class="card-body bg-[#D5C4F3]">
            <h5 class="card-title">Submit File</h5>
            <form action="{{ route('students.projects.submit_file', $project->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-2">
                    <label for="file_type" class="form-label">File Type</label>
                    <select name="file_type" id="file_type" class="form-select" required>
                        <option value="">Select Task</option>
                        @foreach ($project->tasks as $task)
                            <option value="{{ $task->title }}">{{ $task->title }}</option>
                        @endforeach
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="mb-2" id="other_file_type_wrapper" style="display: none;">
                    <label for="other_file_type" class="form-label">Specify File Type</label>
                    <input type="text" name="other_file_type" id="other_file_type" class="form-control">
                </div>
                <div class="mb-2">
                    <label for="file" class="form-label">Upload File</label>
                    <input type="file" name="file" id="file" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success btn-sm">Submit File</button>
            </form>
        </div>
    </div>
</div>


    <div class="col-md-9 project-timeline-section" >
    <div class="card">
        <div class="card-body bg-[#B9C8FF]">
            <h5 class="card-title">Project Timeline</h5>
            <button class="btn btn-primary btn-sm mb-3" id="openAddTaskModal">+ Add Task</button>
            <div id="gantt_here" style="width: 100%; height: 300px; "></div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css">
<script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>

<div class="row mt-3">
    <!-- Submitted Files -->
    <div class="col-12">
        <h5>Submitted Files</h5>
        <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>File Type</th>
                        <th>Version</th>
                        <th>View</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($project->files->groupBy('file_type') as $fileType => $files)
                        @foreach ($files as $file)
                            <tr>
                                <td>{{ ucfirst(str_replace('_', ' ', $fileType)) }}</td>
                                <td>Version {{ $file->version }}</td>
                                <td>
                                    <a href="{{ Storage::url($file->file_path) }}" target="_blank" class="btn btn-link">View</a>
                                </td>
                                <td>
                                    <span class="badge 
                                        @if ($file->approval_status === 'approved') bg-success
                                        @elseif ($file->approval_status === 'rejected') bg-danger
                                        @else bg-warning
                                        @endif">
                                        {{ ucfirst($file->approval_status) }}
                                    </span>
                                </td>
                                <td>
                                    @if ($file->comment)
                                        <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#commentModal{{ $file->id }}">View Comment</button>
                                        <!-- Comment Modal -->
                                        <div class="modal fade" id="commentModal{{ $file->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Supervisor's Comment</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">{{ $file->comment }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        No comment yet
                                    @endif
                                    @if (auth()->user()->isStudent())
                                        <form action="{{ route('students.projects.delete_file', $file->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No files submitted yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>



</div>


</div>

 <!-- Leaderboard Tab -->
 <div class="tab-pane fade" id="leaderboard" role="tabpanel" aria-labelledby="leaderboard-tab">
    <h3 class="mb-4 text-center">🏆 Project Leaderboard (Points-Based) 🏆</h3>
    <table class="table table-bordered">
        <thead>
            <tr class="text-center">
                <th>Rank</th>
                <th>Medal</th>
                <th>Project Title</th>
                <th>Students</th>
                <th>Points</th>
                <th>Progress</th>
            </tr>
        </thead>
        <tbody>
            @php
                // Prepare leaderboard data sorted by points
                $leaderboard = \App\Models\Project::with('tasks', 'students')
                    ->where('supervisor_id', $project->supervisor_id)
                    ->get()
                    ->map(function ($proj) {
                        $totalTasks = $proj->tasks->count();
                        $completedTasks = $proj->tasks->where('status', 'completed')->count();
                        $overdueTasks = $proj->tasks->where('status', 'overdue')->count();
                        $progress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

                        // Calculate points
                        $points = ($completedTasks * 10); // 10 points per completed task
                        if ($progress === 100) {
                            $points += 50; // Bonus for full completion
                        }
                        $points -= ($overdueTasks * 2); // Penalty for overdue tasks

                        return [
                            'title' => $proj->title,
                            'students' => $proj->students->pluck('name')->join(', '),
                            'progress' => $progress,
                            'points' => $points,
                        ];
                    })
                    ->sortByDesc('points') // Sort by points
                    ->values();
            @endphp

            @forelse ($leaderboard as $index => $proj)
                <tr class="text-center">
                    <!-- Rank -->
                    <td>{{ $index + 1 }}</td>
                    
                    <!-- Medals for Top Ranks -->
                    <td>
                        @if ($index == 0)
                            🥇 <!-- Gold Medal -->
                        @elseif ($index == 1)
                            🥈 <!-- Silver Medal -->
                        @elseif ($index == 2)
                            🥉 <!-- Bronze Medal -->
                        @else
                            🎖️ <!-- Participation Medal -->
                        @endif
                    </td>

                    <!-- Project Title -->
                    <td>{{ $proj['title'] }}</td>

                    <!-- Students -->
                    <td>{{ $proj['students'] }}</td>

                    <!-- Points -->
                    <td>{{ $proj['points'] }} pts</td>

                    <!-- Progress with Badges -->
                    <td>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar 
                                @if ($proj['progress'] >= 75) bg-success
                                @elseif ($proj['progress'] >= 50) bg-info
                                @elseif ($proj['progress'] >= 25) bg-warning
                                @else bg-danger
                                @endif"
                                role="progressbar"
                                style="width: {{ $proj['progress'] }}%;"
                                aria-valuenow="{{ $proj['progress'] }}"
                                aria-valuemin="0" aria-valuemax="100">
                                {{ $proj['progress'] }}%
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No projects available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

        </div>

    @else
        <p class="text-center">No project assigned yet.</p>
    @endif
</div>

<!-- Task Modal -->
<div class="modal fade" id="taskModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="taskModalLabel">Add New Task</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="taskForm" action="{{ route('project.add_task', ['projectId' => $project->id, 'userId' => auth()->user()->id]) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="title" class="form-label">Task Title</label>
                                <input type="text" name="title" id="title" class="form-control" >
                                @error('start_date')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" >
                                @error('due_date')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="due_date" class="form-label">Due Date</label>
                                <input type="date" name="due_date" id="due_date" class="form-control" >
                                @error('title')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
               
    
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Add Task</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="customTaskModal" tabindex="-1" aria-labelledby="customTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="customTaskForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="customTaskModalLabel">Update Task Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Task:</strong> <span id="customTaskTitle"></span></p>
                    <input type="hidden" id="customTaskId" name="taskId">
                    <div class="mb-3">
                    <label for="customTaskTitle" class="form-label">Task Title</label>
                    <input type="text" id="customTaskTitleInput" name="title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="customTaskStartDate" class="form-label">Start Date</label>
                    <input type="date" id="customTaskStartDate" name="start_date" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="customTaskDueDate" class="form-label">Due Date</label>
                    <input type="date" id="customTaskDueDate" name="due_date" class="form-control" required>
                </div>

                    <div class="mb-3">
                        <label for="customTaskStatus" class="form-label">Status</label>
                        <select id="customTaskStatus" name="status" class="form-control" required>
                            <option value="todo">To Do</option>
                            <option value="in progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>



<script>
    document.getElementById('file_type').addEventListener('change', function () {
        const otherFileTypeWrapper = document.getElementById('other_file_type_wrapper');
        const otherFileTypeInput = document.getElementById('other_file_type');

        if (this.value === 'other') {
            otherFileTypeWrapper.style.display = 'block';
            otherFileTypeInput.required = true;
        } else {
            otherFileTypeWrapper.style.display = 'none';
            otherFileTypeInput.required = false;
        }
    });
</script>
<script>
      document.addEventListener('DOMContentLoaded', function () {
        gantt.config.date_format = "%Y-%m-%d";
    gantt.config.columns = [
        { name: "text", label: "Task name", width: "*", tree: true },
        { name: "start_date", label: "Start Date", align: "center" },
        { name: "duration", label: "Duration", align: "center" },
     
    ];

    gantt.config.lightbox.sections = [
        { name: "description", height: 38, map_to: "text", type: "textarea", focus: true },
        { name: "status", height: 38, map_to: "status", type: "select", options: [
            { key: "todo", label: "To Do" },
            { key: "in progress", label: "In Progress" },
            { key: "completed", label: "Completed" }
        ]},
        { name: "time", type: "duration", map_to: "auto" }
    ];

    // CSS class assignment for color control
    gantt.templates.task_class = function (start, end, task) {
    const today = new Date();
    const dueDate = gantt.date.parseDate(task.due_date, "xml_date");

    if (task.status === 'overdue') {
        return "overdue"; // Red if overdue and not completed
    } else if (task.status === 'in progress') {
        return "in-progress";
    } else if (task.status === 'completed') {
        return "completed";
    } else {
        return "todo";
    }
};


function calculateProgress() {
    const tasks = gantt.getTaskByTime(); // Get all tasks in the Gantt chart
    const totalTasks = tasks.length;
    const completedTasks = tasks.filter(task => task.status === 'completed').length;
    const progressPercentage = totalTasks > 0 ? (completedTasks / totalTasks) * 100 : 0;
    const taskProgress = totalTasks > 0 ? (completedTasks / totalTasks) * 100 : 0;

    // Update the progress bar
    document.querySelector('.progress-bar').style.width = `${progressPercentage}%`;
    document.querySelector('.progress-bar').setAttribute('aria-valuenow', progressPercentage);
    document.querySelector('.progress-bar').textContent = `${Math.round(progressPercentage)}%`;

    const remainingTasks = totalTasks - completedTasks;
        document.getElementById('progressText').textContent = `Progress: ${Math.round(taskProgress)}% - ${remainingTasks} task(s) left to complete 100%`;
}

    // Load tasks from the server
    function loadTasks() {
    fetch(`/projects/{{ $project->id }}/tasks`)
        .then(response => response.json())
        .then(data => {
            console.log("Tasks loaded:", data); // Debugging to verify status data
            const tasks = data.data.map(task => ({
                id: task.id,
                text: task.title || task.text,
                start_date: task.start_date,
                duration: task.duration,
                status: task.status || 'todo', // Default to 'todo' if status is missing
                due_date: task.due_date
            }));

            gantt.clearAll();
            gantt.parse({ data: tasks });
            calculateProgress();
        })
        .catch(error => console.error("Error loading tasks:", error));
}
// CSS to define colors based on task color property


     // Show the Add Task modal on button click
     document.getElementById('openAddTaskModal').addEventListener('click', () => {
        $('#taskModal').modal('show');
    });

    // Handle Add Task form submission
    document.querySelector('#taskForm').addEventListener('submit', function (event) {
    event.preventDefault();
    const form = event.target;

    // Clear previous errors
    const errorElements = document.querySelectorAll('.text-danger');
    errorElements.forEach(el => el.remove());

    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: new FormData(form)
    })
    .then(response => {
        if (!response.ok) {
            if (response.status === 422) {
                // Handle validation errors
                return response.json().then(data => {
                    const errors = data.errors || {};
                    Object.keys(errors).forEach(key => {
                        const input = document.querySelector(`[name="${key}"]`);
                        if (input) {
                            const errorDiv = document.createElement('div');
                            errorDiv.classList.add('text-danger');
                            errorDiv.textContent = errors[key][0]; // Display first error for the field
                            input.parentNode.appendChild(errorDiv);
                        }
                    });
                });
            }
            throw new Error('An unexpected error occurred. Please try again later.');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            console.log("Task added:", data.task);

            // Close the modal
            $('#taskModal').modal('hide');

            // Clear the form fields
            form.reset();

            // Refresh the Gantt chart
            loadTasks();
        }
    })
    .catch(error => {
        alert(`An error occurred: ${error.message}`);
    });
});

     

       


   // Disable the default lightbox and open custom modal instead
   gantt.attachEvent("onTaskClick", function (id, e) {
        const task = gantt.getTask(id);
        openCustomModal(task);
        return false; // Prevents default lightbox from opening
    });

    // Function to open custom modal
    function openCustomModal(task) {
        // Fill in the modal fields with the task data
        document.getElementById('customTaskTitle').innerText = task.text;
        document.getElementById('customTaskId').value = task.id;
        document.getElementById('customTaskTitleInput').value = task.text;
    document.getElementById('customTaskStartDate').value = gantt.date.date_to_str("%Y-%m-%d")(task.start_date);
    document.getElementById('customTaskDueDate').value = task.due_date
        document.getElementById('customTaskStatus').value = task.status || 'todo';

        // Show the modal
        $('#customTaskModal').modal('show');
    }

    // Handle the custom modal form submission
    document.getElementById('customTaskForm').addEventListener('submit', function (event) {
        event.preventDefault();

        const taskId = document.getElementById('customTaskId').value;
        const newTitle = document.getElementById('customTaskTitleInput').value;
        const newStartDate = document.getElementById('customTaskStartDate').value;
        const newDueDate = document.getElementById('customTaskDueDate').value;
        const newStatus = document.getElementById('customTaskStatus').value;

        // Update the task in the Gantt chart
        const task = gantt.getTask(taskId);
    if (newTitle) task.text = newTitle;
    if (newStartDate) task.start_date = gantt.date.str_to_date("%Y-%m-%d")(newStartDate);
    if (newDueDate) task.end_date = gantt.date.str_to_date("%Y-%m-%d")(newDueDate);
    task.status = newStatus;

    gantt.updateTask(taskId);

        // Send the update to the server
        sendTaskUpdate(taskId, { title: newTitle, start_date: newStartDate, due_date: newDueDate, status: newStatus });

        // Close the modal
        $('#customTaskModal').modal('hide');
        calculateProgress();
    });

    function sendTaskUpdate(id, data) {

        const requestData = {};

// Add only non-empty fields to the request
if (data.title) requestData.title = data.title;
if (data.start_date) requestData.start_date = data.start_date;
if (data.due_date) requestData.due_date = data.due_date;
if (data.status) requestData.status = data.status;
        fetch(`/task/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(requestData),
        })
        .then(response => response.json())
        .then(data => {
            console.log("Task updated in database:", data);
            loadTasks(); // Optional: Refresh tasks if needed
        })
        .catch(error => console.error("Error updating task:", error));
    }

    gantt.init("gantt_here");
    loadTasks();
});
        </script>

<link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


<style>
  .gantt_task_line.todo {
        background-color: #007bff !important; /* Blue for To Do */
    }
    .gantt_task_line.in-progress {
        background-color: #ffa500 !important; /* Orange for In Progress */
    }
    .gantt_task_line.completed {
        background-color: #28a745 !important; /* Green for Completed */
    }
    .gantt_task_line.overdue {
        background-color: #ff4c4c !important; /* Red for Overdue */
    }
</style>
@endsection

<style>
    /* Remove bottom margin from Task Completion Progress */
   
    .task-completion-section2 {
        margin-left: -140px !important; 
        width: 690px !important;
        height: 200px !important;
    }
    /* Reduce top margin for Project Timeline */
    .project-timeline-section {
        margin-top: -400px !important; /* Adjust this value to your liking */
        padding-top: 0 !important;
    }

    .file-submission-section {
        margin-left: -8px !important; 
        width: 320px !important;/* Adjust this value to your liking */
        margin-top: 230px;
        padding-top: 0 !important;
        height:400px !important;
    }

    .project-section {
       
        width: 400px !important;/* Adjust this value to your liking */
        
        padding-top: 0 !important;
    }


    .progress-bar {
    transition: width 0.5s ease;
    font-size: 14px;
    font-weight: bold;
    color: white;
}

.progress-bar[aria-valuenow="100"] {
    background-color: #28a745; /* Dark Green for 100% */
}

.table td, .table th {
    vertical-align: middle;
}

h3.text-center {
    color: #6c757d; /* Muted color for title */
    font-weight: bold;
}
</style>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

