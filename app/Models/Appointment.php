<?php
// app/Models/Appointment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Appointment extends Model
{
    use HasFactory;

    protected $fillable = ['student_id', 'slot_id', 'status', 'request_reason', 'date','start_time','end_time','supervisor_id', 'Gmeet_link','supervisor_google_event_id','project_id'];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function slot()
    {
        return $this->belongsTo(Slot::class);
    }

    public function logbook()
    {
        return $this->hasOne(Logbook::class);
    }

    public function project()
{
    return $this->belongsTo(Project::class, 'project_id');
}

}