<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    protected $table = 'attendances';

    protected $fillable = [
        'employee_id',
        'date',
        'shift_start_at',
        'user_entry_time',
        'is_late',
        'shift_end_at',
        'user_exit_time',
        'is_early',
        'manual_entry',
        'manual_entry_by',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function manualEntryBy()
    {
        return $this->belongsTo(User::class, 'manual_entry_by');
    }


    // Accessor to format shift_start_at
    public function getFormattedShiftStartAtAttribute()
    {
        return Carbon::parse($this->shift_start_at)->format('h:i A');
    }

    // Accessor to format user_entry_time
    public function getFormattedUserEntryTimeAttribute()
    {
        return Carbon::parse($this->user_entry_time)->format('h:i A');
    }

    // Accessor to format shift_end_at
    public function getFormattedShiftEndAtAttribute()
    {
        return Carbon::parse($this->shift_end_at)->format('h:i A');
    }

    // Accessor to format user_exit_time
    public function getFormattedUserExitTimeAttribute()
    {
        return Carbon::parse($this->user_exit_time)->format('h:i A');
    }
}
