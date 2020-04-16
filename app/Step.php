<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Step extends Model
{

    protected $fillable = [
        'message', 'skippable', 'uploadable', 'bot_id', 'payload', 'step_order'
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function bot()
    {
        return $this->belongsTo('App\Bot');
    }
}
