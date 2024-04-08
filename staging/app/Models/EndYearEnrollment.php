<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EndYearEnrollment extends Model
{
    protected $table = "endyear_enrollments";

    protected $fillable = [
            'name',
            'account',
            'account_type',
            'phone',
            'email',
            'source',
            'new_account',
            'deposit',
            'endyear_packages_id',
            'sales_name',
    ];
}
