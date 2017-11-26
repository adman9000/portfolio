<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

     public function adminShortcuts() {

        return $this->hasMany('App\AdminShortcut');

    }

    public function hasAdminShortcut($url) {

        foreach($this->adminShortcuts as $shortcut) {
            if($shortcut['url'] == $url) return true;
        }
        return false;
    }


    //relationships
    public function coins() {

        return $this->hasMany('App\Modules\Portfolio\UserCoin');

    }

    public function transactions() {
        return $this->hasMany('App\Modules\Portfolio\Transaction');
    }

    public function exchanges() {

        return $this->hasMany('App\Modules\Portfolio\UserExchange');
    }

     public function userValues() {

        return $this->hasMany('App\Modules\Portfolio\UserValue');

    }
}
