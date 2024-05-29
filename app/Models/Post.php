<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $table = 'posts';

    protected $primaryKey = 'p_id';

    protected $fillable = [
        'p_title',
        'p_content',
        'p_image',
        'p_video',
        'p_type',
        'p_user_id',
    ];

    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(User::class,'p_user_id','u_id');
    }
}
