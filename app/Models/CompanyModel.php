<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyModel extends Model
{
    use HasFactory;
    
    protected $table = 'companies';

    protected $primaryKey = 'c_id';

    protected $fillable = [
        'c_name',
        'c_name_ar',
        'c_owner_id',
        'c_category_id',
        'c_phone',
        'c_latitude',
        'c_longitude',
        'c_image'
    ];

    public $timestamps = true;

    public function category()
    {
        return $this->belongsTo(CategoryModel::class,'c_category_id','cc_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class,'c_owner_id','u_id');
    }

}
