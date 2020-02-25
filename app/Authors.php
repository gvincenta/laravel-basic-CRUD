<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Authors
 * @package App
 * Defines many to many relationship between books and authors.
 */
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
