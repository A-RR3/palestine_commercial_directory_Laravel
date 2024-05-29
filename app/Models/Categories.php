<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'company_categories';

    protected $primaryKey = 'cc_id';

    protected $fillable = [
        'cc_name',
    ];

    // public function company()
    // {
    //     return $this->hasMany(companies::class);
    // }

}
