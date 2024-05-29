<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolesModel extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $primaryKey = 'r_id';

    protected $fillable = [
        'r_name',
    ];

    public function user()
    {
        return $this->hasMany(User::class);
    }


}
