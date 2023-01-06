<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'users';
    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'address',
        'phone',
        'is_expert',
        'wallet',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];



    public function userDetails()
    {
        # code...
        return $this->hasOne(ExpertDetails::class);
    }

    public function expertDays()
    {
        # code...
        return $this->hasMany(ExpertDays::class);
    }

    public function expertConsultings()
    {
        # code...
        return $this->hasMany(ExpertConsultings::class);
    }

    public function favorite_list()
    {
        return $this->hasMany(FavoriteList::class);
    }

    public function ratings()
    {
        return $this->hasMany(ExpertRatings::class);
    }
}
