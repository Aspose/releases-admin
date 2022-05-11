<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AmazonS3Setting extends Model
{
    protected $fillable = ['bucketname', 'apikey', 'apisecret'];

    
}
