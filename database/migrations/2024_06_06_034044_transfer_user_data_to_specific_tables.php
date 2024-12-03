<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Student;
use App\Models\Supervisor;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Transfer data from users table to students and supervisors tables
        User::chunk(100, function ($users) {
            foreach ($users as $user) {
                if ($user->role === 'student') {
                    // Ensure matric_number is not null
                    if ($user->matric_number) {
                        Student::create([
                            'student_id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'matric_number' => $user->matric_number,
                            'department' => $user->department,
                            'contact_number' => $user->contact_number,
                        ]);
                    } else {
                        // Optionally log or handle the case where matric_number is null
                        \Log::warning('Skipping user with ID ' . $user->id . ' due to null matric_number.');
                    }
                } elseif ($user->role === 'supervisor' || $user->role === 'coordinator') {
                    // Ensure staff_id is not null
                    if ($user->staff_id) {
                        Supervisor::create([
                            'supervisor_id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'staff_id' => $user->staff_id,
                            'contact_number' => $user->contact_number,
                            'office_address' => $user->office_address,
                            'department' => $user->department,
                            'is_coordinator' => $user->role === 'coordinator',
                        ]);
                    } else {
                        // Optionally log or handle the case where staff_id is null
                        \Log::warning('Skipping user with ID ' . $user->id . ' due to null staff_id.');
                    }
                }
            }
        });
    }

    public function down()
    {
        // Rollback the data transfer
        Student::truncate();
        Supervisor::truncate();
    }
};
