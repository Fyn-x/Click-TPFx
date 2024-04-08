<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekretCustomField extends Model
{
    protected $connection = 'mysql_crm';

    protected $table = "tblcustomfieldsvalues";
}
