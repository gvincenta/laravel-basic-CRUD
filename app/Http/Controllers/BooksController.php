<?php

namespace App\Http\Controllers;

use App\Authors;
use App\Books;
use Faker\Provider\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Controls book on database.
 *  A book has a title and an author.
*/
class BooksController extends Controller
{
    public function exportToXML(Request $request)
    {
        $results = Books::all();
        return FileExportController::exportToXML($results,[Books::TABLE_NAME],[],[Books::FIELDS],FileExportController::XML_DATA_TAG);

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
