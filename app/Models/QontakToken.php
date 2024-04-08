<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QontakToken extends Model
{
    protected $table = "qontak_tokens";

    protected $fillable = [
            'id',
            'access_token',
            'refresh_token'
    ];
}
