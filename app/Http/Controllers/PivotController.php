<?php

namespace App\Http\Controllers;

use App\Authors;
use App\Books;
use App\Exports\DBExport;
use App\Exports\PivotExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PivotController extends Controller
{
    public const XML_BOOKS_AND_AUTHORS_PATH = "books-and-authors";
    public const XML_AUTHORS_AND_BOOKS_PATH = "authors-and-books";
    private $exportUtility, $export;
    public function __construct()
    {
        $this->exportUtility = new ExportUtilityController();

    }
    public function index(){
        return DB::table('authors_books')
            ->rightJoin(Authors::TABLE_NAME, 'authors.authorID', '=', 'authors_books.authors_authorID')
            ->leftJoin(Books::TABLE_NAME, 'books.bookID', '=', 'authors_books.books_bookID')
            ->select('authors.authorID', 'authors.name', 'books.bookID', 'books.title');
    }


    public function exportToXML(Request $request){
        print $request->path();
        if ( Str::contains($request->path(), PivotController::XML_BOOKS_AND_AUTHORS_PATH )){
            $results = Books::with('authors')->get();
                return $this->exportUtility->exportToXML($results,[PivotController::XML_BOOKS_AND_AUTHORS_PATH, Authors::TABLE_NAME],
                    [Authors::TABLE_NAME], [Books::FIELDS,Authors::FIELDS], ExportUtilityController::XML_DATA_TAG);

        } else if(Str::contains($request->path(), PivotController::XML_AUTHORS_AND_BOOKS_PATH )){
            $results = Authors::with('books')->get();
            return $this->exportUtility->exportToXML($results,[PivotController::XML_AUTHORS_AND_BOOKS_PATH,Books::TABLE_NAME],
                [Books::TABLE_NAME], [Authors::FIELDS,Books::FIELDS], ExportUtilityController::XML_DATA_TAG);
        }

    }
    public function exportToCSV(){
        $query = $this->index();
        $this->export = new DBExport( $query->get(),$query->columns);
        return $this->exportUtility->exportToCSV($this->export,'authors.csv');


    }
}
