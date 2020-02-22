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
    private $export,$utility;
    public const XML_BOOKS_AND_AUTHORS_PATH = "with-authors";
    public const DELETE_A_BOOK_SUCCEED_MESSAGE = "deleting a book succeed";
    public const DELETE_A_BOOK_FAILED_MESSAGE = "deleting a book failed";
    public const BOOKS_EXPORT_CSV_FILENAME = 'books.csv';
    public function __construct()
    {
        $this->utility = new UtilityController();

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
            return $this->utility->exportToXML($results,[Books::TABLE_NAME, Authors::TABLE_NAME],
                [Authors::TABLE_NAME], [Books::FIELDS,Authors::FIELDS], UtilityController::XML_DATA_TAG);

        }
        return $this->utility->exportToXML(Books::all(),
            [Books::TABLE_NAME],[],[Books::FIELDS],UtilityController::XML_DATA_TAG);
    }

    /**
     * exports all books stored in the database to CSV.
     * @returns a CSV file.
     */
    public function exportToCSV()
    {
        $data = Books::all();
        $this->export = new DBExport( $data , $this->utility->extractHeadings($data));
        return $this->utility->exportToCSV($this->export,self::BOOKS_EXPORT_CSV_FILENAME);
    }

    /**
     * Stores a new book in database.
     * @param   String $title, the title of the book.
     * @return Integer,   id of the book.
     */
    public function store($title  )
    {
         return DB::table(Books::TABLE_NAME)->insertGetId([Books::TITLE_FIELD => $title ]);
    }

    /**
     * Remove the specified book from database.
     * @param  \Illuminate\Http\Request  $request, containing ID of the book to be deleted.
     * @return \Illuminate\Http\JsonResponse the number of rows deleted, if request is valid.
     * @return \Illuminate\Http\JsonResponse invalid request, if request is invalid.
     */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            Books::ID_FIELD => 'required|numeric'
        ]);
        if ($validator->fails()) {
            return  response()->json(
                [UtilityController::MESSAGE_RESPONSE_KEY => UtilityController::INVALID_REQUEST_MESSAGE],
                UtilityController::INVALID_REQUEST_STATUS);
        }

        $affectedRows= DB::table(Books::TABLE_NAME)
            ->where(Books::ID_FIELD, "=", $request->input(Books::ID_FIELD))
            ->delete();
         //for completed delete:
        if ($affectedRows == 1){
            return  response()->json(
                [UtilityController::MESSAGE_RESPONSE_KEY => self::DELETE_A_BOOK_SUCCEED_MESSAGE ],
                UtilityController::OK_STATUS);
        //for failed delete (i.e. no rows affected):
        }else{
            return  response()->json(
                [UtilityController::MESSAGE_RESPONSE_KEY => self::DELETE_A_BOOK_FAILED_MESSAGE ],
                UtilityController::OK_STATUS);
        }
    }
}
