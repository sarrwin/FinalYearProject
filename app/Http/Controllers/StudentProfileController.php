<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentProfileController extends Controller
{
    public function index()
    {
        $students = User::where('role', 'student')->get();
        return view('/supervisor/students.index', compact('students'));
    }

    public function edit()
    {
        $user = Auth::user();
        return view('students.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'matric_number' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:255',
            'department' => 'nullable|string|in:Artificial Intelligence,Software Engineering,Computer System and Network,Multimedia,Information System',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();

        // Update profile picture
        if ($request->hasFile('profile_picture')) {
            $oldPicture = public_path('uploads/' . $user->profile_picture);

            // Delete old profile picture if it exists
            if ($user->profile_picture && file_exists($oldPicture)) {
                unlink($oldPicture);
            }

            // Save new profile picture
            $file = $request->file('profile_picture');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $filename);

            $user->profile_picture = $filename;
        }

        // Update other profile details
        $user->update([
            'contact_number' => $request->contact_number,
            'department' => $request->department,
        ]);

        if ($user->role === 'student') {
            $student = Student::where('student_id', $user->id)->first();
    
            if ($student) {
                $student->update([
                    'contact_number' => $request->contact_number,
                    'department' => $request->department,
                ]);
            }
        }

        return redirect()->route('students.profile.edit')
            ->with('success', 'Profile updated successfully.');
    }

    public function show($id)
    {
        // Retrieve the student with their associated user and current project
        $student = User::where('id', $id)->with('projects')->firstOrFail();
        return view('students.profile.show', compact('student'));
    }
    

    // public function showD($id)
    // {
    //     $student = User::findOrFail($id);
    //     return view('students.dashboard', compact('student'));
    // }
}
