<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpertRatings extends Model
{
    use HasFactory;
    protected $fillable = [
        'expert_id',
        'user_id',
        'rating'
    ];

}
