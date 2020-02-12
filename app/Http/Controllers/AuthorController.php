<?php

namespace App\Http\Controllers;

use App\Authors;
use App\Books;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use XMLWriter;
use FetchLeo\LaravelXml\Facades\Xml;

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
    //function adapted from : https://stackoverflow.com/questions/30014960/how-can-i-store-data-from-mysql-to-xml-in-laravel-5
    //for exporting author only / with book titles to xml
    public function exportAuthorsToXML(Request $request){

        $validatedData = $request->validate([
            'titles' => 'required',
            'authors' => 'required'
        ]);

        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->startDocument();
        $results = "";
        //for exporting both titles and authors:
         if ($validatedData['titles'] && $validatedData['authors'] ){

            $results = Authors::with('books')->get();
            $xml->startElement('authors-with-books');



             return parent::exportToXMLHelper($results,["authors-with-books","books"],["books"],
                [['authorID','name','created_at','updated_at'],['bookID','title','created_at','updated_at']],
                "data");

             }
             //for exporting authors only:
          else if ($validatedData['authors'] && !$validatedData['titles'] ){
              $results = Authors::all();

              return parent::exportToXMLHelper($results,["authors"],[],
                  [['authorID','name','created_at','updated_at']],"data");
         }
        //encloses the xml tags and return it:

        $xml->endElement();
        $xml->endDocument();

        $content = $xml->outputMemory();
        $xml = null;

        return response($content)->header('Content-Type', 'text/xml');

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
