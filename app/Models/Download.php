<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Download extends Model
{
    protected $fillable = ['FileID', 'LOGID', 'Email','family','product','folder','etag_id','IsCustomer','IPAddress','UrlReferrer','UserName','TimeStamp'];

    
}
