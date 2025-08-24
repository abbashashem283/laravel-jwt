<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthRevoke extends Model
{
    protected $fillable = [
        'user_id',
        'status'
    ];

    public function users() {
        return $this->hasMany(User::class);
    }
}
