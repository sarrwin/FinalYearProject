<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogbookFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'logbook_entry_id',
        'file_path',
        'file_type',
    ];

    public function logbookEntry()
    {
        return $this->belongsTo(LogbookEntry::class);
    }
}
