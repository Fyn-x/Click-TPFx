<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekretLead extends Model
{
    protected $connection = 'mysql_crm';

    protected $table = "tblleads";

    public $timestamps = false;

    protected $fillable = [
        'is_qontak_broadcast'
    ];
}
