<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Authors extends Model
{
    protected $table = 'authors';
    public $primaryKey = 'authorID';
    public const FIELDS =  ['authorID','name','created_at','updated_at'];
    public const TABLE_NAME = "authors";

    public function books()
    {
        return $this->belongsToMany(Books::class);

    }
}
