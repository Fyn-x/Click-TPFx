<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QontakWebhook extends Model
{
    protected $table = "qontak_webhooks";
    protected $fillable = [
        'text'
    ];
}
