<?php
// app/Http/Controllers/SupervisorController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class SupervisorController extends Controller
{
    public function index()
    {
        $supervisors = User::where('role', 'supervisor')->get();
        return view('/students/supervisors.index', compact('supervisors'));
    }

    public function show(User $supervisor)
    {
        if ($supervisor->role !== 'supervisor') {
            abort(404);
        }

        return view('/students/supervisors.show', compact('supervisor'));
    }
}
