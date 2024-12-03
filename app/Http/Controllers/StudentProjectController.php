<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class StudentProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with('supervisor', 'students')->get();
        return view('students.projects.index', compact('projects'));
    }
}