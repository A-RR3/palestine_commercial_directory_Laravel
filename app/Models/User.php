<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory,HasApiTokens;

    protected $table = 'users';

    protected $primaryKey = 'u_id';

    protected $fillable = [
        'u_name',
        'u_name_ar',
        'u_phone',
        'u_image',
        'password',
        'u_role_id',
        'u_status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'u_role' => 'string',
        'u_status' => 'boolean',
    ];

    // public function role()
    // {
    //     return $this->hasOne(RolesModel::class);
    // }

    public function companies()
    {
        return $this->hasMany(CompanyModel::class,'c_owner_id','u_id');
    }

    public function posts()
    {
        return $this->hasMany(PostModel::class,'p_user_id','u_id');
    }

    public function likes()
    {
        return $this->hasMany(LikeModel::class,'l_user_id','u_id');
    }

    public function device()
    {
        return $this->hasMany(DeviceToken::class,'d_user_id','u_id');
    }

    // public function tokens()
    // {
    //     return $this->hasMany(PersonalAccessToken::class, 'tokenable_id', 'u_id');
    // }

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::deleting(function ($user) {
    //         // Delete associated access tokens
    //         $user->tokens()->delete();
    //     });
    // }

}
