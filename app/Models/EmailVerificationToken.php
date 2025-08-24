<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailVerificationToken extends Model
{
    public $timestamps = false;

    protected $fillable = [
        "user_id",
        "user_email",
        "token",
        "iat",
        "exp"
    ];

    public function user() {
        return $this->belongsTo(User::class) ;
    }
}
