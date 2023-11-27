<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Waad\Media\Traits\HasOneMedia;

class Post extends Model
{
    use HasOneMedia;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
