<?php
namespace App\Http\Controllers;

use App\Models\Logbook;
use App\Models\LogbookEntry;
use App\Models\LogbookFile;
use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogbookController extends Controller
{
    public function create()
    {
        $projects = Auth::user()->assignedProjects()->get();
        return view('students.logbook.create', compact('projects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'activity' => 'required|string',
            'activity_date' => 'required|date',
            'reference_file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg',
            'report_file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg',
        ]);
    
        $logbook = Logbook::firstOrCreate(['project_id' => $request->project_id]);
    
        $logbookEntry = LogbookEntry::create([
            'logbook_id' => $logbook->id,
            'student_id' => Auth::id(),
            'activity' => $request->activity,
            'activity_date' => $request->activity_date,
            'verified' => false,
        ]);
    
        if ($request->hasFile('reference_file')) {
            $referenceFilePath = $request->file('reference_file')->store('logbook_files','public');
            LogbookFile::create([
                'logbook_entry_id' => $logbookEntry->id,
                'file_path' => $referenceFilePath,
                'file_type' => $request->file('reference_file')->getClientOriginalExtension(),
            ]);
        }
    
        if ($request->hasFile('report_file')) {
            $reportFilePath = $request->file('report_file')->store('logbook_files');
            LogbookFile::create([
                'logbook_entry_id' => $logbookEntry->id,
                'file_path' => $reportFilePath,
                'file_type' => $request->file('report_file')->getClientOriginalExtension(),
            ]);
        }
    
        return redirect()->route('students.logbook.create')->with('success', 'Logbook entry created successfully.');
    }

    public function edit(LogbookEntry $logbookEntry)
    {
        if ($logbookEntry->student_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('students.logbook.edit', compact('logbookEntry'));
    }

    public function update(Request $request, LogbookEntry $logbookEntry)
    {
        if ($logbookEntry->student_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'activity' => 'required|string',
            'activity_date' => 'required|date',
            'reference_file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg',
            'report_file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg',
        ]);

        $logbookEntry->update([
            'activity' => $request->activity,
            'activity_date' => $request->activity_date,
        ]);

        if ($request->hasFile('reference_file')) {
            $referenceFilePath = $request->file('reference_file')->store('logbook_files' , 'public');
            LogbookFile::create([
                'logbook_entry_id' => $logbookEntry->id,
                'file_path' => $referenceFilePath,
                'file_type' => $request->file('reference_file')->getClientOriginalExtension(),
            ]);
        }

        if ($request->hasFile('report_file')) {
            $reportFilePath = $request->file('report_file')->store('logbook_files');
            LogbookFile::create([
                'logbook_entry_id' => $logbookEntry->id,
                'file_path' => $reportFilePath,
                'file_type' => $request->file('report_file')->getClientOriginalExtension(),
            ]);
        }

        return redirect()->route('students.logbook.index')->with('success', 'Logbook entry updated successfully.');
    }

    public function destroy(LogbookEntry $logbookEntry)
    {
        if ($logbookEntry->student_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $logbookEntry->delete();

        return redirect()->route('students.logbook.index')->with('success', 'Logbook entry deleted successfully.');
    }

    public function showFile(LogbookFile $logbookFile)
{
    $user = Auth::user();
    $logbookEntry = $logbookFile->logbookEntry;
    $project = $logbookEntry->logbook->project;

    // // Check if the user is the owner of the logbook entry or the supervisor of the project
    // if ($user->id !== $logbookEntry->student_id && $user->id !== $project->supervisor_id) {
    //     abort(403, 'Unauthorized action.');
    // }

    return response()->file(storage_path('app/' . $logbookFile->file_path));
}
    private function isSupervisorOfProject($user, $project)
    {
        return $user->role === 'supervisor' && $user->id === $project->supervisor_id;
    }

    public function index(Project $project)
    {
        $logbook = Logbook::where('project_id', $project->id)
                          ->with(['entries.student', 'entries.logbookFiles'])
                          ->firstOrFail();
    
        return view('supervisor.logbook.index', compact('logbook', 'project'));
    }

    public function indexStudent()
    {
        $projects = Auth::user()->assignedProjects()->get();

        $project = Project::whereHas('students', function ($query) {
            $query->where('student_id', Auth::id());
        })->first();
    
        // Fetch all logbook entries related to the project (all students)
        $logbookEntries = LogbookEntry::whereHas('logbook.project', function ($query) use ($project) {
            $query->where('project_id', $project->id);
        })->with(['logbook.project', 'logbookFiles'])->get();
        // $logbookEntries = LogbookEntry::where('student_id', Auth::id())->with(['logbook.project', 'logbookFiles'])->get();
        return view('students.logbook.index', compact('logbookEntries','projects'));
    }

    public function viewStudentLogbooks(User $student)
    {
        $logbookEntries = LogbookEntry::where('student_id', $student->id)
                                      ->with(['logbook.project', 'logbookFiles'])
                                      ->get();
        return view('supervisor.logbook.view_student', compact('logbookEntries', 'student'));
    }

    public function verify(LogbookEntry $logbookEntry)
    {
        if ($logbookEntry->logbook->project->supervisor_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $logbookEntry->update(['verified' => true]);

        return redirect()->route('supervisor.logbook.index', $logbookEntry->logbook->project_id)->with('success', 'Logbook entry verified successfully.');
    }

    public function uploadReferenceDocument(Request $request, LogbookEntry $logbookEntry)
    {
        if ($logbookEntry->student_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg'
        ]);

        $filePath = $request->file('file')->store('logbook_files');

        LogbookFile::create([
            'logbook_entry_id' => $logbookEntry->id,
            'file_path' => $filePath,
            'file_type' => $request->file->getClientOriginalExtension(),
        ]);

        return redirect()->route('students.logbook.index')->with('success', 'Reference document uploaded successfully.');
    }

    public function uploadReportDocument(Request $request, LogbookEntry $logbookEntry)
    {
        if ($logbookEntry->student_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg'
        ]);

        $filePath = $request->file('file')->store('logbook_files');

        LogbookFile::create([
            'logbook_entry_id' => $logbookEntry->id,
            'file_path' => $filePath,
            'file_type' => $request->file->getClientOriginalExtension(),
        ]);

        return redirect()->route('students.logbook.index')->with('success', 'Report document uploaded successfully.');
    }

    public function downloadFile(LogbookFile $logbookFile)
    {
        $user = Auth::user();
        $logbookEntry = $logbookFile->logbookEntry;

        // Check if the user is the owner of the logbook entry or a supervisor of the project
        if ($user->id !== $logbookEntry->student_id && !$this->isSupervisorOfProject($user, $logbookEntry->logbook->project)) {
            abort(403, 'Unauthorized action.');
        }

        return response()->download(storage_path('app/' . $logbookFile->file_path));
    }
}
