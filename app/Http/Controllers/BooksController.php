<?php

namespace App\Http\Controllers;

use App\Authors;
use App\Books;
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
    public const DELETE_A_BOOK_SUCCEED_MESSAGE = "deleting a book succeed";
    public const DELETE_A_BOOK_FAILED_MESSAGE = "deleting a book failed";
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
            $results = Books::with(Authors::TABLE_NAME)->get();
            return $this->exportUtility->exportToXML($results,[Books::TABLE_NAME, Authors::TABLE_NAME],
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
            Books::ID_FIELD => 'required|numeric'
        ]);
        if ($validator->fails()) {
            return  response()->json(['message' => "invalid request"], 400);
        }

        $affectedRows= DB::table(Books::TABLE_NAME)
            ->where(Books::ID_FIELD, "=", $request->input(Books::ID_FIELD))
            ->delete();
         //for completed delete:
        if ($affectedRows == 1){
            return  response()->json(['message' => self::DELETE_A_BOOK_SUCCEED_MESSAGE ], 200);
        //for failed delete (i.e. no rows affected):
        }else{
            return  response()->json(['message' => self::DELETE_A_BOOK_FAILED_MESSAGE ], 200);
        }
    }
}
