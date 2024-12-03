<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended($this->redirectPath($request->user()));
        }

        return view('auth.verify-email');
    }

    protected function redirectPath($user)
    {
        return match ($user->role) {
            'admin' => 'admin/dashboard',
            'supervisor' => 'supervisor/dashboard',
            'students' => 'student/dashboard',
            'coordinator' => 'coordinator/dashboard',
            default => 'dashboard',
        };
    }

}

