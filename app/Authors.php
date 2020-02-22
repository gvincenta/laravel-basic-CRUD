<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Authors extends Model
{
    protected $table = 'authors';
    public $primaryKey = 'authorID';
    public const FIELDS =  ['authorID','firstName','lastName'];
    public const TABLE_NAME = "authors";
    public const ID_FIELD = "authorID";
    public const FIRSTNAME_FIELD = "firstName";
    public const LASTNAME_FIELD = "lastName";


    public function books()
    {
        return $this->belongsToMany(Books::class);

    }
}
