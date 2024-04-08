<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Source extends Model
{
    protected $connection = 'mysql_crm';

    protected $table = "tblleads_sources";
}
