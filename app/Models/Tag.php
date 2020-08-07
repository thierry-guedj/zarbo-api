<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $table = 'taggable_tags';

    public function design()
    {
        return $this->belongsToMany(Design::class, 'design_id');
    }
}
