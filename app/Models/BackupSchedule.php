<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupSchedule extends Model
{
    protected $table = 'user_backup_schedule';
    protected $fillable = ['user_id','label', 'time', 'days', 'status', 'is_instant','is_continuous'];

    protected $casts = [
        'days'       => 'array',
        'status'    => 'boolean',
        'is_instant' => 'boolean',
    ];
}
