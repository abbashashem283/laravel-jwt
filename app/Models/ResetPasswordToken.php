<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResetPasswordToken extends Model
{

    public $timestamps = false ;

    protected $fillable = [
        'user_id',
        'user_email',
        'token',
        'exp'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
