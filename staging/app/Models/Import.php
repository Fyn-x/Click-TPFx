<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Import extends Model
{
    protected $table = "imports";
    public $timestamps = false;

    protected $fillable = [
            'name',
            'email',
            'phone'
    ];
}
