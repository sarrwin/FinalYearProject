<?php
// app/Models/Slot.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slot extends Model
{
    use HasFactory;

    protected $fillable = ['supervisor_id', 'date', 'start_time', 'end_time', 'meeting_details','booked','project_id'];
    protected $casts = [
        'date' => 'datetime',
        'end_time' => 'datetime', // If you have an end time
    ];
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}