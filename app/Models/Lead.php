<?php

namespace App\Models;

use Carbon\Carbon;
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

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s');
    }
}
