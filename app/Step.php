<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Step extends Model
{

    protected $fillable = [
        'message', 'skippable', 'bot_id', 'payload', 'step_order'
    ];

    public function bot()
    {
        return $this->belongsTo('App\Bot');
    }
}
