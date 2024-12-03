<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Supervisor;

class SupervisorProfileController extends Controller
{

    public function index()
    {
        $students = User::where('role', 'student')->get();
        //dd($students); // Debugging line to check data
        return view('supervisor.students.index', compact('students'));
    }
    
    public function edit()
    {
        $supervisor = Auth::user(); // Get the current supervisor
        $projects = $supervisor->projects()->with('students')->get(); // Fetch related projects with students
    
        // Fetch all unique sessions for the dropdown
        $sessions = $supervisor->projects()->distinct()->pluck('session');
    
        return view('supervisor.profile.edit', compact('supervisor', 'projects', 'sessions'));
    }
    public function update(Request $request)
    {
        $request->validate([
            'contact_number' => 'nullable|string|max:255',
            'office_address' => 'nullable|string|max:255',
            'area_of_expertise' => 'nullable|string|max:255',
            'department' => 'nullable|string|in:Artificial Intelligence,Software Engineering,Computer System and Network,Multimedia,Information System',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
    
        $user = Auth::user(); // Current supervisor user
    
        // Update profile picture
        if ($request->hasFile('profile_picture')) {
            $oldPicture = public_path('uploads/' . $user->profile_picture);
    
            // Delete the old profile picture if it exists
            if ($user->profile_picture && file_exists($oldPicture)) {
                unlink($oldPicture);
            }
    
            // Store the new profile picture
            $file = $request->file('profile_picture');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $filename);
    
            // Save the new profile picture path
            $user->profile_picture = $filename;
        }
    
        // Update supervisor-specific information
        $user->update([
            'contact_number' => $request->contact_number,
            'office_address' => $request->office_address,
            'department' => $request->department,
        ]);
    
        return redirect()->route('supervisor.profile.edit', $user->id)
            ->with('success', 'Profile updated successfully.');
    }
    

    //For other user to view
    public function show($id, Request $request)
    {
        $supervisor = User::where('id', $id)->with('projects')->firstOrFail();
    
        // Fetch all unique sessions for the dropdown
        $sessions = $supervisor->projects()->distinct()->pluck('session');
    
        // Filter projects by session if provided
        $projects = $supervisor->projects();
        if ($request->has('session') && $request->session) {
            $projects->where('session', $request->session);
        }
    
        $projects = $projects->with('students')->get();
    
        return view('supervisor.profile.show', compact('supervisor', 'projects', 'sessions'));
    }
}
