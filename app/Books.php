<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Books
 * @package App
 * Defines many to many relationship between books and authors.
 */
class Books extends Model
{
    protected $table = 'books';
    public $primaryKey = 'bookID';
    public const FIELDS = ['bookID','title'];
    public const TABLE_NAME = "books";
    public const ID_FIELD = "bookID";
    public const TITLE_FIELD = "title";
    protected $fillable = ['title'];
    public function authors()
    {
        return $this->belongsToMany(Authors::class);
    }
}
