<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;
    
    protected $table = 'companies';

    protected $primaryKey = 'c_id';

    protected $fillable = [
        'c_name',
        'c_holder_id',
        'c_category_id',
        'c_phone',
        'c_latitude',
        'c_longitude'
    ];

    public $timestamps = true;

    public function category()
    {
        return $this->belongsTo(Category::class,'c_category_id','cc_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class,'c_holder_id','u_id');
    }

}
