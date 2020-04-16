<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bot extends Model
{

    protected $fillable = [
        'name', 'token', 'callback', 'owner'
    ];

    public function getRouteKeyName()
    {
        return 'callback';
    }

    public function steps()
    {
        return $this->hasMany('App\Step');
    }

    public function geApiAttribute()
    {
        return 'https://api.telegram.org/bot' . $this->token . '/';
    }


}
