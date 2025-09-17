<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pull extends Model
{
    protected $fillable = [
        'item_id',
        'session_key',
        'username',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
