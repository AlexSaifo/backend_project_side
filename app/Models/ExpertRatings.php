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
    public function experts(){
        return $this->belongsTo(User::class);
    }

    public function users()
    {
        return $this->belongsTo(User::class);
    }

}
