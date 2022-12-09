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
        'rate',
        'user_id',
        'updated_at',
        'created_at'

    ];


    public function users(){
        return $this->belongsTo(User::class);
    }
}
