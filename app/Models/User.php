<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    protected $table = 'users';

    protected $primaryKey = 'u_id';

    protected $fillable = [
        'u_name',
        'u_phone',
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

    public function role()
    {
        return $this->hasOne(Roles::class);
    }

}
