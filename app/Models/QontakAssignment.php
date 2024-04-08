<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QontakAssignment extends Model
{
    protected $fillable = [
        'agent_id',
        'leads_phone_number',
        'is_assigned'
    ];
}
