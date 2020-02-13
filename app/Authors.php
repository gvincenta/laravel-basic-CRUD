<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Authors extends Model
{
    protected $table = 'authors';
    public $primaryKey = 'ID';
    public const FIELDS =  ['ID','firstName','lastName'];
    public const TABLE_NAME = "authors";

    public function books()
    {
        return $this->belongsToMany(Books::class);

    }
}
