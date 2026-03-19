<?php

namespace Tests\App\Models;

use Illuminate\Database\Eloquent\Model;
use Waad\Media\HasMedia;

class Post extends Model
{
    use HasMedia;

    protected $fillable = ['title'];
}
