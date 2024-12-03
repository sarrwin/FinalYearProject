<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatRoom extends Model
{
    protected $fillable = [
        'supervisor_id', 
        'title',
        'is_announcement',
        'project_id',  // Add project_id
    ];

    public function supervisor()
{
    return $this->belongsTo(Supervisor::class, 'supervisor_id', 'supervisor_id');
}

    public function students()
    {
        return $this->belongsToMany(User::class, 'chatroom_student', 'chatroom_id', 'student_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
