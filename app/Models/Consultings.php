<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultings extends Model
{
    use HasFactory;
    protected $table = 'consultings';
    protected $fillable = [
        'id',
        'name'
    ];

    public function expertConsultings()
    {
        # code...
        return $this->hasMany(ExpertConsultings::class);
    }
}
