<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Books extends Model
{
    protected $table = 'books';
    public $primaryKey = 'bookID';
    protected $fillable = ['title'];
    public function authors()
    {
        return $this->belongsToMany(Authors::class);
    }
}
