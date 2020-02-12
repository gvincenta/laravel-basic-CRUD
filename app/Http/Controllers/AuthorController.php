<?php

namespace App\Http\Controllers;

use App\Authors;
use App\Books;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthorController extends Controller
{



    //returns an author alongside his/her books.
    public function show($name){
        $result = Authors::with('books')->get();
        $result->where("name","=","$name")->get();
        return $result->toJson();

    }
    public function update(Request $request){
        $validatedData = $request->validate([
            'name' => 'required',
            'authorID' => 'required'
        ]);

        return json_encode(DB::update('UPDATE authors SET name = "' . $validatedData['name'] . '" WHERE authorID =' . $validatedData['authorID']  ));
    }
    public function getSortedAuthors()
    {
        $result = Authors::with('books')->orderBy('name')->get();
        return $result->toJson();

    }



    /**
     * Store a newly created book in database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'bookID' => 'required'
        ]);

        $author = new Authors;
        $author->name = $validatedData['name'];

        $author->save();
        $book = Books::find($validatedData['bookID']);
         $author->books()->attach($book);

        return response()->json('author created!');
    }








}
