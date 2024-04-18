<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class SPV extends Model
{
    protected $connection = 'mysql_crm';

    protected $table = "tblstaff";

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s');
    }
}
