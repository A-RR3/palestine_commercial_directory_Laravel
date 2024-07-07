<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceToken extends Model
{
    use HasFactory;

    protected $table = 'device_tokens';

    protected $primaryKey = 'd_id';

    protected $fillable = [
        'd_user_id', 
        'device_token', 
        'device_type'
    ];

    // Define the relationship to the User model (if applicable)
    public function user()
    {
        return $this->belongsTo(User::class);
        
    }
}
