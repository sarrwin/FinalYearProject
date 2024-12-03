<?php

namespace App\Http\Controllers;
use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use App\Models\ProjectFile;
use App\Models\ChatRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use App\Mail\FileApprovalNotification;

class ProjectController extends Controller
{
    public function indexSupervisorProjects()
    {
        $projects = Auth::user()->projects()->with('students', 'files')->get();
        return view('supervisor.students.projects.index', compact('projects'));
    }

    public function viewSupervisorProject(Project $project)
    {
        if ($project->supervisor_id === null || $project->supervisor_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $project->load('students', 'files');
        return view('supervisor.students.projects.view', compact('project'));
    }

    public function viewStudentProject(User $student)
    {
        $project = $student->assignedProjects()->with('supervisor', 'students', 'files','tasks')->first();

        if ($project->supervisor_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('supervisor.students.projects.view_student_project', compact('project'));
    }

    public function myProject()
    {
        $project = Auth::user()->assignedProjects()->with('supervisor', 'students', 'files')->first();
        return view('students.projects.my_project', compact('project'));
    }
    public function submitFile(Request $request, Project $project)
    {
        \Log::info('File submission started.', [
            'project_id' => $project->id,
            'student_id' => Auth::id(),
            'file_type' => $request->file_type,
        ]);
    
        $request->validate([
            'file_type' => 'required|string',
            'file' => 'required|file',
        ]);
    
        $fileType = $request->file_type === 'other' ? $request->other_file_type : $request->file_type;
    
        \Log::info('File type determined.', ['file_type' => $fileType]);
    
        // Check for existing files of the same type for versioning
        $existingFile = ProjectFile::where('project_id', $project->id)
            ->where('student_id', Auth::id())
            ->where('file_type', $fileType)
            ->latest('version') // Get the highest version
            ->first();
    
        // Increment version if a file exists, otherwise start at version 1
        $version = $existingFile ? $existingFile->version + 1 : 1;
    
        \Log::info('Version determined.', [
            'existing_file_id' => $existingFile->id ?? null,
            'previous_version' => $existingFile->version ?? null,
            'new_version' => $version,
        ]);
    
        $path = $request->file('file')->store('project_files', 'public');
    
        \Log::info('File stored successfully.', [
            'file_path' => $path,
        ]);
    
        // Save the new file with incremented version
        ProjectFile::create([
            'project_id' => $project->id,
            'student_id' => Auth::id(),
            'file_type' => $fileType,
            'file_path' => $path,
            'version' => $version, // Correct version assignment
            'original_name' => $request->file('file')->getClientOriginalName(),
        ]);
    
        \Log::info('File submission completed.', [
            'project_id' => $project->id,
            'student_id' => Auth::id(),
            'file_type' => $fileType,
            'version' => $version,
        ]);
    
        return redirect()->route('students.projects.my_project')->with('success', 'File submitted successfully.');
    }
    public function showLeaderboard()
{
    // Fetch all projects supervised by the authenticated supervisor
    $projects = Project::with(['tasks', 'students'])
        ->where('supervisor_id', Auth::id())
        ->get();

    // Prepare leaderboard data
    $leaderboard = $projects->map(function ($project) {
        // Calculate progress percentage
        $totalTasks = $project->tasks->count();
        $completedTasks = $project->tasks->where('status', 'completed')->count();
        $progress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        return [
            'title' => $project->title,
            'students' => $project->students,
            'progress' => $progress,
        ];
    });

    // Sort projects by progress in descending order
    $sortedLeaderboard = $leaderboard->sortByDesc('progress')->values();
    \Log::info('Leaderboard Data:', $sortedLeaderboard->toArray());

    return view('supervisor.leaderboard', ['projects' => $sortedLeaderboard]);
}

    public function viewFile(ProjectFile $projectFile)
    {
        $filePath = storage_path('app/' . $projectFile->file_path);
    
        if (!file_exists($filePath)) {
            abort(404, 'File not found.');
        }
    
        return response()->download($filePath);
    }
    public function indexAllProjects()
    {
        $projects = Project::with('supervisor', 'students')->paginate(10); ;
        return view('students.projects.index_all', compact('projects'));
    }
    

    public function index()
    {
        $projects = Project::where('supervisor_id', Auth::id())->with('students')->get();
        return view('supervisor.projects.index', compact('projects'));
    }

    public function create()
    {
        $students = User::where('role', 'student')->get();
        return view('supervisor.projects.create', compact('students'));
    }
    public function addComment(Request $request, $fileId)
{
    $request->validate([
        'comment' => 'required|string',
        'approval_status' => 'required|in:pending,approved,rejected',
    ]);

    $file = ProjectFile::findOrFail($fileId);
    $file->comment = $request->input('comment');
    $file->approval_status = $request->input('approval_status'); // Update the approval status
    $file->save();

    // Send email notification to the student
    $student = $file->student;
    $details = [
      'student_name' => $student->name,
        'file_type' => $file->file_type,
        'approval_status' => $file->approval_status,
        'comment' => $file->comment,
    ];
    \Log::info('Email Details:', $details);

    Mail::to($student->email)->send(new FileApprovalNotification($details));

    return redirect()->back()->with('success', 'Comment and approval status updated successfully.');
}

    public function deleteFile($fileId)
    {
        $file = ProjectFile::findOrFail($fileId);
        Storage::delete($file->file_path);
        $file->delete();

        return redirect()->route('students.projects.my_project')->with('success', 'File deleted successfully.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'students_required' => 'required|integer|min:1',
            'students' => 'array', // Ensure students field is an array
            'students.*' => 'exists:users,id', // Ensure each student exists
            'session' => 'required|string|max:255',
        'department' => 'required|string|in:Artificial Intelligence,Software Engineering,Computer System and Network,Multimedia,Information System',
        'tools' => 'required|string|max:255',
        ]);

        $project = Project::create([
            'supervisor_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'students_required' => $request->students_required,
            'session' => $request->session,
             'department' => $request->department,
            'tools' => $request->tools,
        ]);

        

        if ($request->students) {
            $assignedStudents = $this->assignStudentsToProject($project, $request->students);

            if (!empty($assignedStudents['alreadyAssigned'])) {
                return redirect()->route('supervisor.projects.create')->with('warning', 'Some students are already assigned to another project: ' . implode(', ', $assignedStudents['alreadyAssigned']));
            }
        }

        return redirect()->route('supervisor.projects.index')->with('success', 'Project created successfully.');
    }

    public function show(Project $project)
    {
        if (Auth::id() !== $project->supervisor_id) {
            abort(403, 'Unauthorized action.');
        }

        return view('supervisor.projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        if (Auth::id() !== $project->supervisor_id) {
            abort(403, 'Unauthorized action.');
        }

        $students = User::where('role', 'student')->get();
        return view('supervisor.projects.edit', compact('project', 'students'));
    }

    public function update(Request $request, Project $project)
    {
        if (Auth::id() !== $project->supervisor_id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'students_required' => 'required|integer|min:1',
            'students' => 'array|max:' . $request->students_required,
            'students.*' => 'exists:users,id',
            'session' => 'required|string|max:255',
            'department' => 'required|string|in:Artificial Intelligence,Software Engineering,Computer System and Network,Multimedia,Information System',
             'tools' => 'required|string|max:255',
        ]);

        $project->update([
            'title' => $request->title,
            'description' => $request->description,
            'students_required' => $request->students_required,
            'session' => $request->session,
            'department' => $request->department,
             'tools' => $request->tools,
        ]);

        if ($request->students) {
            $assignedStudents = $this->assignStudentsToProject($project, $request->students);

            if (!empty($assignedStudents['alreadyAssigned'])) {
                return redirect()->route('supervisor.projects.edit', $project->id)->with('warning', 'Some students are already assigned to another project: ' . implode(', ', $assignedStudents['alreadyAssigned']));
            }
            $this->ensureProjectRoom($project);
        }

        return redirect()->route('supervisor.projects.index')->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        if (Auth::id() !== $project->supervisor_id) {
            abort(403, 'Unauthorized action.');
        }

        $project->delete();
        return redirect()->route('supervisor.projects.index')->with('success', 'Project deleted successfully.');
    }

    public function indexStudent()
    {
        $projects = Auth::user()->assignedProjects()->with('supervisor')->get();
        return view('students.projects.index', compact('projects'));
    }

    private function assignStudentsToProject(Project $project, array $studentIds)
{
    $alreadyAssigned = [];
    $assigned = [];

    // Detach existing students
    $project->students()->detach();

    // Assign each student to the project
    foreach ($studentIds as $studentId) {
        $student = User::find($studentId);
        if ($student && !$student->assignedProjects()->exists()) {
            $project->students()->attach($studentId);
            $assigned[] = $student->name;
        } else {
            $alreadyAssigned[] = $student->name;
        }
    }

    // Ensure general announcement room exists
    $this->ensureAnnouncementRoom($project->supervisor_id);

    // Create project-specific chat room
    $this->createProjectRoom($project);

    return ['assigned' => $assigned, 'alreadyAssigned' => $alreadyAssigned];
}

    public function showProjects(Project $project)
    {
        return view('students.projects.details', compact('project'));
    }

    private function ensureAnnouncementRoom($supervisorId)
{
     // Ensure the supervisor has a general announcement room
     $chatRoom = ChatRoom::firstOrCreate([
        'supervisor_id' => $supervisorId,
        'is_announcement' => true,
    ], [
        'title' => 'General Announcement Room',
    ]);

    // Fetch students supervised by this supervisor (you may adjust this based on your relationships)
    $students = User::whereHas('assignedProjects', function ($query) use ($supervisorId) {
        $query->where('supervisor_id', $supervisorId);
    })->pluck('id')->toArray();

    // Link the students to the announcement room
    $chatRoom->students()->syncWithoutDetaching($students);
}

private function createProjectRoom(Project $project)
{
    // Find the existing project room by project ID
    $chatRoom = ChatRoom::where('project_id', $project->id)->first();

    if ($chatRoom) {
        // If a room exists, update its title with the new project title
        $chatRoom->update([
            'title' => 'Project Room: ' . $project->title,
        ]);
    } else {
        // If no room exists, create a new project room
        $chatRoom = ChatRoom::create([
            'supervisor_id' => $project->supervisor_id,
            'project_id' => $project->id,  // Ensure project_id is set
            'title' => 'Project Room: ' . $project->title,
            'is_announcement' => false,
        ]);
    }

    // Attach or sync students to the chat room
    $chatRoom->students()->sync($project->students->pluck('id')->toArray());
}



private function ensureProjectRoom($project)
{
    // Find the existing project room using project_id
    $chatRoom = ChatRoom::where('project_id', $project->id)->first();

    if ($chatRoom) {
        // If a room exists, update its title with the new project title
        $chatRoom->update([
            'title' => 'Project Room: ' . $project->title,
        ]);
    } else {
        // If no room exists, create a new one
        $chatRoom = ChatRoom::create([
            'supervisor_id' => $project->supervisor_id,
            'project_id' => $project->id,  // Set the project_id
            'title' => 'Project Room: ' . $project->title,
            'is_announcement' => false,
        ]);
    }

    // Fetch the students assigned to the project
    $students = $project->students->pluck('id')->toArray();

    // Link the students to the existing or newly created project chat room
    $chatRoom->students()->syncWithoutDetaching($students);
}


public function getTasks($projectId)
{
    try {
        \Log::info("Fetching tasks for project ID: $projectId");

        $project = Project::with('tasks')->findOrFail($projectId);
        
        \Log::info("Project found:", $project->toArray());

        $tasks = $project->tasks->map(function ($task) {
            return [
                'id' => $task->id,
                'text' => $task->title,
                'start_date' => $task->start_date->format('Y-m-d'),
                'due_date' => $task->due_date->format('Y-m-d'),
                'duration' => $task->start_date->diffInDays($task->due_date) + 1,
                'status' => $task->status 
            ];
        });

        \Log::info("Tasks mapped:", $tasks->toArray());

        return response()->json(['data' => $tasks]);

    } catch (\Exception $e) {
        \Log::error("Error fetching tasks: " . $e->getMessage());
        return response()->json(['error' => 'Could not load tasks'], 500);
    }
}


public function addTask(Request $request, $projectId, $userId)
{
    $messages = [
        'title.required' => 'The task title is required.',
        'start_date.required' => 'The start date is required.',
        'due_date.required' => 'The due date is required.',
    ];

    try {
        $request->validate([
            'title' => 'required|string|max:255',
            'start_date' => 'required|date|before_or_equal:due_date',
            'due_date' => 'required|date|after_or_equal:start_date',
        ], $messages);

        $project = Project::findOrFail($projectId);

        $task = $project->tasks()->create([
            'title' => $request->title,
            'start_date' => $request->start_date,
            'due_date' => $request->due_date,
            'status' => 'todo',
        ]);

        return response()->json([
            'success' => true,
            'task' => $task,
        ]);
    } catch (ValidationException $e) {
        return response()->json([
            'success' => false,
            'errors' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        \Log::error('Error adding task: ' . $e->getMessage(), ['userId' => $userId, 'projectId' => $projectId]);
        return response()->json([
            'success' => false,
            'error' => 'An unexpected error occurred.',
        ], 500);
    }
}


public function updateTask(Request $request, $id)
{
    \Log::info("Update request received for task ID: $id", $request->all()); // Log all request data

    $task = Task::findOrFail($id);

    // Update fields based on request data
    if ($request->has('title')) {
        $task->title = $request->title;
        \Log::info("Updating title to: " . $task->title);
    }
    if ($request->has('start_date')) {
        $task->start_date = $request->start_date;
        \Log::info("Updating start_date to: " . $task->start_date);
    }
    if ($request->has('due_date')) {
        $task->due_date = $request->due_date;
        \Log::info("Updating due_date to: " . $task->due_date);
    }
    if ($request->has('status')) {
        $task->status = $request->status;
        \Log::info("Updating status to: " . $task->status);
    } else {
        \Log::warning("No status provided in request, keeping current status: " . $task->status);
    }

    $task->save(); // Save changes to the database

    \Log::info("Task updated in database:", $task->toArray()); // Confirm updated task data in logs

    return response()->json(['success' => true, 'task' => $task]);
}
public function deleteTask($id)
{
    $task = Task::findOrFail($id);
    $task->delete();

    return response()->json(['success' => true]);
}









}
