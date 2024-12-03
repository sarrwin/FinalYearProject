<?php
// namespace app/Http/Controllers/HomeController.php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\ChatRoom;
use App\Models\Messages;
use App\Models\User;
class HomeController extends Controller
{
    public function coordinatorDashboard()
    {
        return view('coordinator.dashboard'); // Create this view
    }

    public function supervisorDashboard(Request $request)
    {
        // Fetch the general announcement room
        $announcementRoom = ChatRoom::where('supervisor_id', Auth::id())
                                    ->where('is_announcement', true)
                                    ->first();
    
        // Fetch project-specific chat rooms
        $projectRooms = ChatRoom::where('supervisor_id', Auth::id())
                                ->where('is_announcement', false)
                                ->get();
    
        // Fetch recent announcements if the announcement room exists
        $announcements = [];
        if ($announcementRoom) {
            $announcements = Messages::where('chatroom_id', $announcementRoom->id)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }
    
        // Handle general announcement submission
       
    
        return view('Supervisor.dashboard', compact('announcementRoom', 'projectRooms', 'announcements'));
    }
    public function postAnnouncement(Request $request)
{
    $request->validate([
        'announcement' => 'required|string|max:500', // Validate the input
    ]);

    // Fetch or create the general announcement room
    $announcementRoom = ChatRoom::firstOrCreate(
        [
            'supervisor_id' => Auth::id(),
            'is_announcement' => true,
        ],
        [
            'title' => 'General Announcement Room',
        ]
    );

    // Add the announcement to the room
    Messages::create([
        'chatroom_id' => $announcementRoom->id,
        'user_id' => Auth::id(),
        'content' => $request->input('announcement'),
    ]);

    return redirect()->route('supervisor.dashboard')->with('success', 'Announcement posted successfully.');
}
public function editAnnouncement(Request $request, $id)
{
    $request->validate([
        'announcement' => 'required|string|max:500',
    ]);

    $announcement = Messages::findOrFail($id);

    if ($announcement->user_id != Auth::id()) {
        abort(403, 'Unauthorized action.');
    }

    $announcement->content = $request->input('announcement');
    $announcement->save();

    return redirect()->route('supervisor.dashboard')->with('success', 'Announcement updated successfully.');
}

public function deleteAnnouncement($id)
{
    $announcement = Messages::findOrFail($id);

    if ($announcement->user_id != Auth::id()) {
        abort(403, 'Unauthorized action.');
    }

    $announcement->delete();

    return redirect()->route('supervisor.dashboard')->with('success', 'Announcement deleted successfully.');
}


    public function studentDashboard()
{
    // Fetch the general announcement room
    $announcementRoom = ChatRoom::whereHas('students', function ($query) {
        $query->where('student_id', Auth::id());
    })->where('is_announcement', true)->first();

    // Fetch project-specific rooms where the student is a member
    $projectRooms = ChatRoom::whereHas('students', function ($query) {
        $query->where('student_id', Auth::id());
    })->where('is_announcement', false)->get();

    // Fetch recent announcements if the announcement room exists
    $announcements = [];
    if ($announcementRoom) {
        $announcements = Messages::where('chatroom_id', $announcementRoom->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    $student = Auth::user()->student;
    return view('students.dashboard', compact('announcementRoom', 'projectRooms', 'student', 'announcements'));
}


    public function redirectToDashboard()
    {
        $user = Auth::user();

        switch ($user->role) {
            case 'student':
                return redirect()->route('students.dashboard');
            case 'supervisor':
                return redirect()->route('supervisor.dashboard');
            case 'coordinator':
                return redirect()->route('coordinator.dashboard');
            default:
                return redirect()->route('dashboard');
        }
    }

    

}
