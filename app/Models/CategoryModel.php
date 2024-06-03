<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryModel extends Model
{
    use HasFactory;

    protected $table = 'company_categories';

    protected $primaryKey = 'cc_id';

    protected $fillable = [
        'cc_name',
    ];

}
