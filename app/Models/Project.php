<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'supervisor_id',
        'title',
        'description',
        'students_required',
        'session',
        'department',
        'tools',
    ];

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'project_student', 'project_id', 'student_id');
    }public function student()
    {
        return $this->belongsToMany(Student::class, 'project_student');
    }
    
    public function files()
    {
        return $this->hasMany(ProjectFile::class);
    }
    public function logbooks()
    {
        return $this->hasMany(Logbook::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
    
    public function calculateProgress()
    {
        $totalTasks = $this->tasks->count();
        $completedTasks = $this->tasks->where('status', 'completed')->count();
    
        return $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
    }
    
    public function tasks()
{
    return $this->hasMany(Task::class);
}

}

