@extends('layouts.app')

@section('content')
<div class="container">
    
    @if ($project)
        <!-- Project Details Section -->
        <div class="card mb-4">
            
            <div class="card-body">
                <h5 class="card-title">Student's Progress</h5>
                <p class="card-text"><strong>PROJECT NAME:</strong> {{ $project->title }}</p>
                <p class="card-text"><strong>SUPERVISOR:</strong> {{ $project->supervisor->name }}</p>
                <p class="card-text"><strong>ASSIGNED STUDENT:</strong>
                    @foreach ($project->students as $student)
                        {{ $student->name }}@if (!$loop->last), @endif
                    @endforeach
                </p>
                <a href="{{ route('supervisor.students.projects.view', $project->id) }}" class="btn btn-secondary">View Project Details</a>
                <a href="{{ route('supervisor.logbook.index', ['project' => $project->id]) }}" class="btn btn-primary">View Logbook</a>
            </div>
        </div>

        <!-- Task Addition Form -->
        
        <!-- Success and Error Messages -->
        @if(session('success'))
            <div class="alert alert-success mt-3">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger mt-3">
                {{ implode(', ', $errors->all()) }}
            </div>
        @endif
        <div class="mb-3">
        <button class="btn btn-primary mb-3" id="openAddTaskModal">+ Add Task</button>
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
                                <input type="text" name="title" id="title" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="due_date" class="form-label">Due Date</label>
                                <input type="date" name="due_date" id="due_date" class="form-control" required>
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

        <h3>Project Timeline</h3>
        <!-- Gantt Chart -->
        <div id="gantt_here" style="width:100%; height:400px; margin-bottom: 20px; overflow-x: auto;"></div>

        <link rel="stylesheet" href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css">
        <script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>

      

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
     

    <!-- Project Progress Section -->
    <div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title">Task Completion Progress</h5>
        <div class="progress mb-3">
            <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" id="taskProgressBar">
                0%
            </div>
        </div>
        <p class="card-text" id="progressText">Calculating progress...</p>
    </div>
</div>

    <!-- Submitted Files Section -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Submitted Files</h5>
            <table class="table table-bordered">
                <thead>
                <tr>
            <th>File Name</th>
            <th>Version</th>
            <th>Submitted by Student</th>
            <th>Comments</th>
            <th>Approval Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($project->files as $file)
            <tr>
                <td>{{ ucfirst(str_replace('_', ' ', $file->file_type)) }}</td>
                <td>{{ $file->version }}</td> <!-- Display version here -->
                <td>{{ $file->student->name }}</td>
                <td>{{ $file->comment ?? 'No comment yet' }}</td>
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
                    <a href="{{ Storage::url($file->file_path) }}" class="btn btn-primary btn-sm" target="_blank">View File</a>
                    <button class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#commentModal" data-file-id="{{ $file->id }}">Add Comment</button>
                </td>
            </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
        <p>No project assigned yet.</p>
    @endif
</div>

<!-- Comment Modal -->
<div class="modal fade" id="commentModal" tabindex="-1" aria-labelledby="commentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="commentModalLabel">Add Comment and Approval</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="commentForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="comment" class="form-label">Comment</label>
                        <textarea name="comment" id="comment" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="approval_status" class="form-label">Approval Status</label>
                        <select name="approval_status" id="approval_status" class="form-select" required>
                            <option value="pending">Pending</option>
                            <option value="approved">Approve</option>
                            <option value="rejected">Reject</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        var commentModal = document.getElementById('commentModal');
        commentModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var fileId = button.getAttribute('data-file-id');
            var form = document.getElementById('commentForm');
            form.action = '/supervisor/students/projects/comment/' + fileId;
        });
    });
</script>

<script>
    document.getElementById('file_type').addEventListener('change', function () {
        var fileType = this.value;
        var otherFileTypeWrapper = document.getElementById('other_file_type_wrapper');
        var otherFileTypeInput = document.getElementById('other_file_type');
        
        if (fileType === 'other') {
            otherFileTypeWrapper.style.display = 'block';
            otherFileTypeInput.name = 'file_type';  // Change the name to file_type
        } else {
            otherFileTypeWrapper.style.display = 'none';
            otherFileTypeInput.name = 'other_file_type';  // Revert the name to avoid conflict
        }
    });
</script>
<script>
      document.addEventListener('DOMContentLoaded', function () {
        gantt.config.date_format = "%Y-%m-%d";
    gantt.config.columns = [
        { name: "text", label: "Task name", width: "*", tree: true },
        { name: "start_date", label: "Start Date", align: "center" },
        { name: "due_date", label: "Due Date", align: "center" },
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

    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: new FormData(form)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log("Task added:", data.task);

            // Close the modal
            $('#taskModal').modal('hide');

            // Clear the form fields
            form.reset();

            // Ensure the date strings are in the right format for Gantt
            const startDate = gantt.date.parseDate(data.task.start_date, "xml_date");
            const endDate = gantt.date.parseDate(data.task.due_date, "xml_date");

            // Add the new task to the Gantt chart
            gantt.addTask({
                id: data.task.id,
                text: data.task.title,
                start_date: startDate,
                duration: gantt.calculateDuration(startDate, endDate),
                status: data.task.status
            });

            loadTasks(); // Refresh the Gantt chart
        } else {
            console.error("Error adding task:", data.error);
        }
    })
    .catch(error => console.error("Error adding task:", error));
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
