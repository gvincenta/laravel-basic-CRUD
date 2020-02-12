<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Books extends Model
{
    protected $table = 'books';
    public $primaryKey = 'bookID';
    public const FIELDS = ['bookID','title','created_at','updated_at'];
    public const TABLE_NAME = "books";
    protected $fillable = ['title'];
    public function authors()
    {
        return $this->belongsToMany(Authors::class);
    }
}
