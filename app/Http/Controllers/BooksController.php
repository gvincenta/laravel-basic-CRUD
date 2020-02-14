<?php

namespace App\Http\Controllers;

use App\Authors;
use App\Books;
use App\Exports\BooksExport;
use App\Exports\DBExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/** Controls the books stored on database.
 *  A book has a title and at least 1 author.
*/
class BooksController extends Controller
{
    private $export,$exportUtility;
    public const XML_BOOKS_AND_AUTHORS_PATH = "with-authors";

    public function __construct()
    {
        $this->exportUtility = new ExportUtilityController();

    }
    /**
     * exports all books stored in the database to XML.
     * @returns XML file.
     */
    public function exportToXML(Request $request)
    {
        //handles request for books and authors XML file. (i.e. books nested with the respective authors).
        if ( Str::contains($request->path(), BooksController::XML_BOOKS_AND_AUTHORS_PATH )){
            $results = Books::with('authors')->get();
            return $this->exportUtility->exportToXML($results,[BooksController::XML_BOOKS_AND_AUTHORS_PATH, Authors::TABLE_NAME],
                [Authors::TABLE_NAME], [Books::FIELDS,Authors::FIELDS], ExportUtilityController::XML_DATA_TAG);

        }
        return $this->exportUtility->exportToXML(Books::all(),
            [Books::TABLE_NAME],[],[Books::FIELDS],ExportUtilityController::XML_DATA_TAG);
    }

    /**
     * exports all books stored in the database to CSV.
     * @returns CSV file.
     */
    public function exportToCSV()
    {
        $data = Books::all();
        $this->export = new DBExport( $data , $this->exportUtility->extractHeadings($data));
        return $this->exportUtility->exportToCSV($this->export,'books.csv');
    }

    /**
     * Stores a new book in database.
     * @param   String $title, the title of the book.
     * @return Integer,   id of the book.
     */
    public function store($title  )
    {
         return DB::table(Books::TABLE_NAME)->insertGetId(["title" => $title ]);
    }

    /**
     * Remove the specified book from database.
     * @param  \Illuminate\Http\Request  $request, containing ID of the book to be deleted.
     * @return \Illuminate\Http\Response the number of rows deleted, if request is valid.
     * @return \Illuminate\Http\Response invalid request, if request is invalid.
     */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ID' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            return ["message" => "invalid request", "code"=>400];
        }

        $affectedRows= DB::table(Books::TABLE_NAME)
            ->where("ID", "=", $request->input('ID'))
            ->delete();
        return ["code" => 200,  "affectedRows" => $affectedRows] ;

    }

    /**
     * Returns a list of sorted books alongside their authors.
     * @return \Illuminate\Http\Response a nested json object of books with authors, sorted A to Z.
     */
     public function getSortedBooks()
    {
         $result = Books::with('authors')->orderBy('title')->get();
        return $result->toJson();
     }
    /**
     * Returns a list of books.
     * @return \Illuminate\Http\Response a nested json object of books with authors (not sorted).
     */
     public function index(){
         return Books::with('authors')->get()->toJson();
     }


}
