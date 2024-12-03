<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectStudent extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'student_id',
       
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    public function students()
    {
        return $this->belongsToMany(Project::class,  'project_id', 'student_id');
    }

    public function files()
    {
        return $this->hasMany(ProjectFile::class);
    }
    public function logbooks()
    {
        return $this->hasMany(Logbook::class);
    }

    public function tasks()
{
    return $this->hasMany(Task::class);
}

}

