<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $connection = 'mysql_leads';

    protected $table = "leads";

    protected $fillable = [
            'name',
            'email',
            'phone',
            'url'
    ];
}
