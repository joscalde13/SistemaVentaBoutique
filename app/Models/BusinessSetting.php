<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessSetting extends Model
{
    protected $fillable = [
        'business_name', 'logo_path', 'address', 'phone', 'email',
        'currency_code', 'currency_symbol',
    ];
}
