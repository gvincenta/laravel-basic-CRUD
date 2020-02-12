<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Authors extends Model
{
    protected $table = 'authors';
    public $primaryKey = 'authorID';
    public function books()
    {
        return $this->belongsToMany(Books::class);
    }
}
