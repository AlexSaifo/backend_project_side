<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpertDays extends Model
{
    use HasFactory;
    protected $table = 'expertdays';
    protected $fillable = [
        'id',
        'start_day',
        'end_day',
        'experts_id',
        'weekdays_id'
    ];

    public function experts()
    {
        # code...
        return $this->hasMany(Expert::class);
    }

    public function weekdays()
    {
        # code...
        return $this->hasMany(WeekDays::class);
    }
}
