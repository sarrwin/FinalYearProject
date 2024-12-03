<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
class Supervisor extends Model
{
    protected $fillable = [
        'supervisor_id', 
        'name', 
        'email', 
        'staff_id', 
        'contact_number', 
        'office_address', 
        'department', 
        'is_coordinator'
    ];
    protected $attributes = [
        'is_coordinator' => false,
    ];
    /**
     * Get the user that owns the supervisor.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'supervisor_id');
    }

     public function slots()
    {
        return $this->hasMany(Slot::class, 'supervisor_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'supervisor_id');
    }

    public function chatRooms()
{
    return $this->hasMany(ChatRoom::class, 'supervisor_id');
}
}
