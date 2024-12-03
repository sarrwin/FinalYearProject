<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Models\Supervisor;
use App\Models\Coordinator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
class AdminController extends Controller
{

    public function index(Request $request)
    {
        $role = $request->input('role');

        // Fetch users based on the selected role
        if ($role) {
            $users = User::where('role', $role)->get();
        } else {
            $users = User::all();
        }
        return view('admin.index', compact('users'));
    }


    public function verifyCoordinator(Supervisor $supervisor): RedirectResponse
{
    $supervisor->update(['is_coordinator' => true]);

    Coordinator::create([
        'user_id' => $supervisor->supervisor_id,
        'department' => $supervisor->department,
        'staff_id' => $supervisor->staff_id, // Include staff_id here
    ]);

    return redirect()->back()->with('success', 'Supervisor verified as coordinator.');
}

public function demoteCoordinator(Supervisor $supervisor): RedirectResponse
{
    $supervisor->update(['is_coordinator' => false]);

    // Delete the corresponding coordinator record
    Coordinator::where('user_id', $supervisor->supervisor_id)->delete();

    return redirect()->back()->with('success', 'Coordinator demoted to supervisor.');
}
}

