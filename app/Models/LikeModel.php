<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LikeModel extends Model
{
    use HasFactory;

    protected $table = 'likes';

    protected $primaryKey = 'l_id';

    protected $fillable = [
        'l_post_id',
        'l_user_id',
    ];
    
    protected $casts = [
        'is_liked' => 'boolean',
    ];

    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(User::class,'l_user_id','u_id');
    }

    public function post()
    {
        return $this->belongsTo(PostModel::class,'l_post_id','p_id');
    }
}
