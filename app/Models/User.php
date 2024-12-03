<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
class User extends Authenticatable 
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'contact_number',
        'office_address',
        'area_of_expertise',
        'department',
        'matric_number', // Add matric_number to fillable properties
        'staff_id',
        'google_token', // Add google_token to fillable properties
        'google_refresh_token', // Add google_refresh_token to fillable properties
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function slots()
    {
        return $this->hasMany(Slot::class, 'supervisor_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'student_id');
    }

    public function isSupervisor()
    {
        return $this->role === 'supervisor';
    }

    public function isStudent()
    {
        return $this->role === 'student';
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    

    public function isCoordinator()
    {
        return $this->role === 'coordinator';
    }
    public function logbooks()
    {
        return $this->hasMany(Logbook::class, 'student_id');
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'supervisor_id');
    }

    public function assignedProjects()
    {
        return $this->belongsToMany(Project::class, 'project_student', 'student_id', 'project_id');
    }

    public function projectFiles()
    {
        return $this->hasMany(ProjectFile::class);
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class,'student_id');
    }

    public function supervisor(): HasOne
    {
        return $this->hasOne(Supervisor::class,'supervisor_id');
    }

    public function coordinator(): HasOne
    {
        return $this->hasOne(Coordinator::class);
    }

    public function chatRooms()
{
    return $this->belongsToMany(ChatRoom::class, 'chatroom_student', 'student_id', 'chatroom_id');
}

public function project()
{
    return $this->belongsTo(Project::class, 'project_id'); // Adjust foreign key if necessary
}
}