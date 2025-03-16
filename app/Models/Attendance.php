<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
