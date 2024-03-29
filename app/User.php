<?php
//https://github.com/milon/laravel-blog
namespace App;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'is_active'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->api_token)) {
                $user->api_token = str_random(50);
            }
        });

        
    }

    

    public function scopeAdmin($query)
    {
        return $query->where('is_admin', true);
    }
}
