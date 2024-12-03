<?php
// app/Models/Logbook.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Logbook extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'student_name',
        'project_id',
        'activity',
        'activity_date',
        'verified',
    ];
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
    protected $casts = [
        'activity_date' => 'date',
    ];
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    public function entries()
    {
        return $this->hasMany(LogbookEntry::class);
    }

    public function files()
    {
        return $this->hasMany(LogbookFile::class);
    }
    
}
