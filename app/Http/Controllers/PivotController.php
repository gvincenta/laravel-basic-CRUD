<?php

namespace App\Http\Controllers;

use App\Authors;
use App\Books;
use App\Exports\DBExport;
use App\Exports\PivotExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * In order to avoid confusion, Pivot refers to the authors_books table that handles the many to many relationship\
 * between them. This controls reading and exporting the authors_books table.
*/
class PivotController extends Controller
{

    public const XML_BOOKS_AND_AUTHORS_PATH = "books-and-authors";
    public const XML_AUTHORS_AND_BOOKS_PATH = "authors-and-books";
    public const TABLE_NAME = "authors_books";
    private $exportUtility, $export;
    public function __construct()
    {
        $this->exportUtility = new ExportUtilityController();

    }
    //returns an author alongside his/her books.
    public function show(Request $request){

        $validator = Validator::make($request->all(), [
            'firstName' => 'required_without:title|string',
            'lastName' =>'required_without:title|string',
            'title' => 'required_without:firstName,lastName|string'
        ]);

        if ($validator->fails()) {
            return ["message" => "invalid request", "code"=>"400"];
        }
         $lastName = "lastName";
        if ($request->get("lastName")){
             return    $this->index()->where('authors.firstName' , '=', $request['firstName'])
                ->where('authors.lastName' , '=', $request['lastName'] )->get();
        } else if ($request->get("title")){
             return    $this->index()->where('books.title' , '=', $request['title'])->get();
        }



    }
    public function store(Request $request){

        /* validation:
         * for existing author(s), we only need their ID
         * for new author(s) to be created, we need their firstName and lastName
         * we also need the book's title to create the new book
         * note: the "string" keyword implicitly eliminates empty string.
         * if validation failed, code does not proceed to the next step. */
        //TODO: Change to validator to avoid infinite loop and return error appropriately.
        $validatedData = $request->validate([
            'title' => 'required|string',
            'authors' => 'required_without:newAuthors',
            'newAuthors'=>'required_without:authors',
            'newAuthors.*.firstName' => 'required_without:authors|string',
            'newAuthors.*.lastName' => 'required_without:authors|string',
            'authors.*.ID' => 'required_without:newAuthors|numeric'
        ]);
        //TODO: breakdown each storing function.

        return DB::transaction(function () use ($validatedData) {
             //firstly create new book:
            $bookID = DB::table("books")->insertGetId(["title" => $validatedData["title"] ]);
            //then, create new authors and assign them as the new book's authors:
            $newAuthorID = [];
            if (array_key_exists("newAuthors",$validatedData)){
                foreach ($validatedData['newAuthors'] as $newAuthor){
                    //make new authors and get their IDs:
                    $newAuthorID = DB::table(Authors::TABLE_NAME)->insertGetId($newAuthor);
                    DB::table(PivotController::TABLE_NAME)->insert(["authors_ID" => $newAuthorID,
                        "books_ID" => $bookID]);
                }
            }
            if (array_key_exists("authors",$validatedData)){
                foreach ($validatedData['authors'] as $existingAuthor){
                    //assign the existing authors as the authors of this book:
                    DB::table(PivotController::TABLE_NAME)->insert(["authors_ID" => $existingAuthor["ID"],
                        "books_ID" => $bookID]);
                }
            }
            return ["message" => "books with their associated authors created successfully", "code"=>"200"] ;



         });

    }
    public function index(){
        return DB::table('authors_books')
            ->rightJoin(Authors::TABLE_NAME, 'authors.ID', '=', 'authors_books.authors_ID')
            ->leftJoin(Books::TABLE_NAME, 'books.ID', '=', 'authors_books.books_ID')
            ->select('authors.ID', 'authors.firstName', 'authors.lastName', 'books.ID', 'books.title');
    }


    public function exportToXML(Request $request){
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
