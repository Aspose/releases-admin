<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Download extends Model
{
    protected $fillable = ['ip_address', 'referrer', 'log'];

    
}
