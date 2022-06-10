<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommitLog extends Model
{
    protected $fillable = ['release_id', 'log'];

    
}
