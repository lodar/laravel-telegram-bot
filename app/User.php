<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'username', 'telegram_id', 'bot_id'
    ];

    protected $with = [
        'bot'
    ];

    public function bot()
    {
        return $this->belongsTo('App\Bot');
    }

    public function chat_logs()
    {
        return $this->hasMany('App\ChatLog');
    }

}
