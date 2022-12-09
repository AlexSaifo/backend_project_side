<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpertConsultings extends Model
{
    use HasFactory;

    protected $table = 'expert_consultings';
    protected $fillable = [
        'id',
        'user_id',
        'consultings_id',
    ];

    public function users()
    {
        # code...
        return $this->belongsTo(User::class);
    }

    public function consultings()
    {
        # code...
        return $this->belongsTo(Consultings::class);
    }
}
