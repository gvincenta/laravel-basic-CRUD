<?php

namespace App\Http\Controllers;

use App\Authors;
use App\Books;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use XMLWriter;
use FetchLeo\LaravelXml\Facades\Xml;

class AuthorsController extends Controller
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
    //for exporting author only / with book titles to xml
    public function exportToXML(Request $request){

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

             if ($request->input('variation') == FileExportController::XML_AUTHORS_WITH_BOOKS){
                $results = Authors::with('books')->get();
                return FileExportController::exportToXML($results,[FileExportController::XML_AUTHORS_WITH_BOOKS,Books::TABLE_NAME],
                 [Books::TABLE_NAME], [Authors::FIELDS,Books::FIELDS], FileExportController::XML_DATA_TAG);
            }
             else if ($request->input('variation') == "books-with-authors"){
                 $results = Books::with('authors')->get();
                  return FileExportController::exportToXML($results,[FileExportController::XML_BOOKS_WITH_AUTHORS, Authors::TABLE_NAME],
                      [Authors::TABLE_NAME], [Books::FIELDS,Authors::FIELDS], FileExportController::XML_DATA_TAG);
             }


             }
             //for exporting authors only:
          else if ($validatedData['authors'] && !$validatedData['titles'] ){
              $results = Authors::all();

              return FileExportController::exportToXML($results,[$authorsDB],[],
                  [$authorsFields],$dataTag);
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
