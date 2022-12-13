<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpertDetails extends Model
{
    use HasFactory;

    protected $table = 'expert_detail';
    protected $fillable = [
        'id',
        'skills',
        'profile_picture',
        'rating',
        'ratings',
        'user_id',
        'cost',
        'updated_at',
        'created_at'

    ];


    public function users(){
        return $this->belongsTo(User::class);
    }
}
