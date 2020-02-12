<?php

namespace App\Http\Controllers;

use App\Authors;
use App\Books;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Controls book on database.
 *  A book has a title and an author.
*/
class BookController extends Controller
{
    public function exportBooksToXML(Request $request)
    {

        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->startDocument();
        $results = Books::all();
        $xml->startElement('books');

        foreach($results as $res) {
            $xml->startElement('data');
            $xml->writeAttribute('bookID', $res->bookID);
            $xml->writeAttribute('title', $res->title);
            $xml->writeAttribute('created_at', $res->created_at);
            $xml->writeAttribute('updated_at', $res->updated_at);
            $xml->endElement();
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
            'title' => 'required'
        ]);

        $book = DB::insert('INSERT INTO books (title) VALUES (?);', [$validatedData['title']]);

        return response()->json('Project created!');
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $validatedData = $request->validate([
            'bookID' => 'required'
        ]);
        return json_encode( DB::delete('DELETE FROM books WHERE bookID = ?',[$validatedData['bookID']]));

    }






    //returns a list of sorted books alongside their authors.
    public function getSortedBooks(){
        //TODO : JOIN WITH author
        $result = Books::with('authors')->orderBy('title')->get();
        return $result->toJson();
//        return  json_encode(DB::table('books')->orderBy('title')->get());
    }
    public function index(Request $request){
        $validatedData = $request->validate([
            'title' => 'required'
        ]);
         return json_encode(DB::table('books')->where("title","=",$validatedData['title'])->get());


    }

}
