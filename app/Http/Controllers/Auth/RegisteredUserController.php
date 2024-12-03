<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

use App\Models\Student;
use App\Models\Supervisor;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255',  'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:student,supervisor,coordinator,admin'],
            'matric_number' => ['required_if:role,student', 'nullable', 'string', 'max:255'],
            'staff_id' => ['required_if:role,supervisor,coordinator', 'nullable', 'string', 'max:255'],
        ]);

        // Create a new user in the users table
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        // Create a record in the students table if the user is a student
        if ($request->role === 'student') {
            Student::create([
                'student_id' => $user->id,
                'name' => $request->name,
                'email' => $request->email,
                'matric_number' => $request->matric_number,
            ]);
        }

        // Create a record in the supervisors table if the user is a supervisor or coordinator
        elseif ($request->role === 'supervisor' || $request->role === 'coordinator') {
            Supervisor::create([
                'supervisor_id' => $user->id,
                'name' => $request->name,
                'email' => $request->email,
                'staff_id' => $request->staff_id,
                'is_coordinator' => false,
            ]);
        }

        // Log in the user
        Auth::login($user);

        // Redirect based on role
        return redirect($this->redirectPath($user));
    }

    protected function redirectPath($user)
    {
        return match ($user->role) {
            'admin' => '/admin/index',
            'supervisor' => '/supervisor/dashboard',
            'student' => '/student/dashboard',
            'coordinator' => '/coordinator/dashboard',
            default => '/dashboard',
        };
    }
}

