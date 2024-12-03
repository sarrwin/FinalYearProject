<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogbookEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'logbook_id',
        'student_id',
        'activity',
        'activity_date',
        'verified',
    ];

    public function logbook()
    {
        return $this->belongsTo(Logbook::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }


    public function logbookFiles()
    {
        return $this->hasMany(LogbookFile::class);
    }
    
}