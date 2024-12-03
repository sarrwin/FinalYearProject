<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Supervisor;
use App\Models\Appointment;
use App\Models\Student;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;

class CoordinatorController extends Controller
{
    public function dashboard()
    {
      $user = Auth::user();
      $supervisor=$user->supervisor;
      $department = $supervisor->department;

        // Total number of students in the coordinator's department
        $totalStudents = Student::where('department', $department)->count();

        // Total number of supervisors in the coordinator's department
        $totalSupervisors = Supervisor::where('department', $department)->count();

        // Total number of projects in the coordinator's department
       $totalProjects = Project::where('department', $department)->count();

        //Total number of students with projects in the coordinator's department
        $studentsWithProjects = Project::where('department', $department)
            ->with('students')
            ->get()
            ->pluck('students')
            ->flatten()
            ->unique('id')
            ->count();

        // Total number of supervisors with projects in the coordinator's department
        $supervisorsWithProjects = Project::where('department', $department)
            ->with('supervisor')
            ->get()
            ->pluck('supervisor')
            ->unique('id')
            ->count();

            return view('coordinator.dashboard', compact('totalStudents', 'totalSupervisors', 'totalProjects', 'studentsWithProjects', 'supervisorsWithProjects'));
    }

    public function showAssignedProjects()
    {
        $user = Auth::user();
        $supervisor = $user->supervisor;
        $department = $supervisor->department;
    
        // Fetch projects within the department
        $projects = Project::where('department', $department)
                            ->with('supervisor')
                            ->get();
    
        // Fetch appointments for the projects
        $appointments = Appointment::with(['supervisor', 'student'])
                                    ->whereIn('student_id', $projects->flatMap(function ($project) {
                                        return $project->students->pluck('id');
                                    }))
                                    ->whereIn('supervisor_id', $projects->pluck('supervisor_id'))
                                    ->get();
    
        return view('coordinator.assigned_projects', compact('projects', 'appointments'));
    }
    
public function projects(){
    $user = Auth::user();
    $supervisor = $user->supervisor;
    $department = $supervisor->department;

    // Fetch projects within the department
    $projects = Project::where('department', $department)
                        ->with('supervisor')
                        ->get();

 return view('coordinator.total_project', compact('projects'));
}

public function viewDepartmentLogbook($projectId)
{
    $user = Auth::user();

    // Ensure the user is a supervisor with coordinator access
    $supervisor = $user->supervisor;

    // Debugging: Check if the supervisor and coordinator status are retrieved correctly
    //dd('Supervisor:', $supervisor, 'Is Coordinator:', $supervisor->is_coordinator);

    if (!$supervisor || !$supervisor->is_coordinator) {
        abort(403, 'Unauthorized access');
    }

    // Retrieve the project if it matches the coordinator's department
    $project = Project::where('id', $projectId)
                      ->where('department', $supervisor->department)
                      ->with(['logbooks.entries.logbookFiles', 'supervisor'])
                      ->first();

    // Debugging: Check if the project data is retrieved and matches the department
    //dd('Department:', $supervisor->department, 'Project Department:', $project->department, 'Project:', $project);

    if (!$project) {
        abort(403, 'Project not found or unauthorized access');
    }

    return view('coordinator.logbook', compact('project'));
}

public function studentsList()
{
    $user = Auth::user();
    $supervisor = $user->supervisor;
    $department = $supervisor->department;

    // Fetch students in the coordinator's department
    $students = Student::where('department', $department)->get();

    return view('coordinator.student_list', compact('students'));
}


}
