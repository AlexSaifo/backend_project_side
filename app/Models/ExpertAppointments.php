<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpertAppointments extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'start_hour',
        'end_hour',
        'user_id',
        'consultant_id'
    ];
}
