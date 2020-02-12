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
            print $results;

             /*data formats:
              * if no book is enlisted:
              * <data authorID="32" name="Gilbert" created_at="2020-02-12 01:03:35" updated_at="2020-02-12 01:03:35"/>
              * if at least 1 book is enlisted:
              * <data authorID="42" name="Adam" created_at="2020-02-12 01:09:40" updated_at="2020-02-12 01:09:40">
              *     <books>
              *         <data bookID="" title="Ugly" created_at="" updated_at=""/>
              *     </books>
              * </data>
              * */

             foreach($results as $res) {
                 $xml->startElement('data');
                 $xml->writeAttribute('authorID', $res->authorID);
                 $xml->writeAttribute('name', $res->name);
                 $xml->writeAttribute('created_at', $res->created_at);
                 $xml->writeAttribute('updated_at', $res->updated_at);

                 //if the author has a book enlisted:

                 if (count($res->books) > 0){
                     $books = $res->books;
                     $xml->startElement('books');
                     foreach($books as $book) {
                         $xml->startElement('data');
                         $xml->writeAttribute('bookID', $book->authorID);
                         $xml->writeAttribute('title', $book->title);
                         $xml->writeAttribute('created_at', $book->created_at);
                         $xml->writeAttribute('updated_at', $book->updated_at);
                         $xml->endElement();
                     }
                     $xml->endElement();

                 }else{
                     $xml->endElement();
                 }


             }
             //for exporting authors only:
         } else if ($validatedData['authors'] && !$validatedData['titles'] ){
             $results = Authors::all();
             $xml->startElement('authors');
             //data formats: <data authorID="32" name="Gilbert" created_at="2020-02-12 01:03:35" updated_at="2020-02-12 01:03:35"/>
             foreach($results as $res) {
                 $xml->startElement('data');
                 $xml->writeAttribute('authorID', $res->authorID);
                 $xml->writeAttribute('name', $res->name);
                 $xml->writeAttribute('created_at', $res->created_at);
                 $xml->writeAttribute('updated_at', $res->updated_at);
                 $xml->endElement();
             }
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
