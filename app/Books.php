<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Books extends Model
{
    protected $table = 'books';
    public $primaryKey = 'ID';
    public const FIELDS = ['ID','title'];
    public const TABLE_NAME = "books";
    protected $fillable = ['title'];
    public function authors()
    {
        return $this->belongsToMany(Authors::class);
    }
}
