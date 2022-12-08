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
        'users_id',
        'weekdays_id',
        
    ];

    public function users()
    {
        # code...
        return $this->hasMany(User::class);
    }

    public function weekdays()
    {
        # code...
        return $this->hasMany(WeekDays::class);
    }
}
