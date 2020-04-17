<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChatLog extends Model
{
    protected $fillable = [
        'step_id', 'user_id', 'response'
    ];

    protected $with = [
        'step',
    ];



    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function step()
    {
        return $this->belongsTo('App\Step');
    }

}
