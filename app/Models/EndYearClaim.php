<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EndYearClaim extends Model
{
    protected $table = "endyear_claims";

    protected $fillable = [
            'name',
            'account',
            'account_type',
            'phone',
            'email',
            'new_account',
            'deposit',
            'date_enroll',
            'endyear_packages_id',
            'sales_name',
            'name_receiver',
            'phone_receiver',
            'address_receiver',
            'phone_sales',
            'name_spv',
    ];
}
