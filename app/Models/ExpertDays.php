<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpertDays extends Model
{
    use HasFactory;
    protected $table = 'expert_days';
    protected $fillable = [
        'id',
        'start_day',
        'end_day',
        'user_id',
        'weekdays_id',

    ];

    public function users()
    {
        # code...
        return $this->belongsTo(User::class);
    }

    public function weekdays()
    {
        # code...
        return $this->belongsTo(WeekDays::class);
    }
}
