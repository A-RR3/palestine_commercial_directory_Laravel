<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    use HasFactory;

    protected $table = 'likes';

    protected $primaryKey = 'l_id';

    protected $fillable = [
        'l_post_id',
        'l_user_id',
    ];

    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(User::class,'l_user_id','u_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class,'l_post_id','p_id');
    }
}
