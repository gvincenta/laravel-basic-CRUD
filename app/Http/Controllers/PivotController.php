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

    public const TABLE_NAME = "authors_books";

    private $exportUtility, $export,$booksController,$authorsController;

    public function __construct()
    {
        $this->exportUtility = new ExportUtilityController();
        $this->booksController = new BooksController();
        $this->authorsController = new AuthorsController();

    }
    /**
     * handles searching for a book through its title/author.
     * @param Illuminate\Http\Request $request, containing the title of the new book and its authors.
     * @return  Illuminate\Http\Response  the book(s) according to title/author, if request is valid.
     * @return Illuminate\Http\Response  invalid request message, if request is not valid.
     */
     public function show(Request $request)
    {
        /* for authors, we need their firstName and lastName.
         * for titles, we need the book's titles. */
        $validator = Validator::make($request->all(), [
            'firstName' => 'required_without:title|string',
            'lastName' =>'required_without:title|string',
            'title' => 'required_without:firstName,lastName|string'
        ]);

        if ($validator->fails()) {
            return ["message" => "invalid request", "code"=>400];
        }
        //search by authors:
         if ($request->get("lastName")){
             return    $this->index()->where('authors.firstName' , '=', $request['firstName'])
                ->where('authors.lastName' , '=', $request['lastName'] )->get();
        }
         //search by titles:
         else if ($request->get("title")){
             return    $this->index()->where('books.title' , '=', $request['title'])->get();
        }
    }
    /**
     * assigns an author to a book.
     */
    public function store($authorID, $bookID  )
    {
        DB::table(PivotController::TABLE_NAME)->insert(["authors_ID" => $authorID,
            "books_ID" => $bookID]);
    }
    /**
     * creates a new book, and also assigns author(s) to it with database's transaction method.
     * if the author(s) don't exist yet in the database, then we also add them to the database.
     * @param Illuminate\Http\Request $request, containing the title of the new book and its authors.
     * @return  Illuminate\Http\Response  success / invalid request message.
     */
    public function createNewBook(Request $request){

        /* validation:
         * for existing author(s), we only need their ID
         * for new author(s) to be created, we need their firstName and lastName
         * we also need the book's title to create the new book
         * note: the "string" keyword implicitly eliminates empty string. */

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'authors' => 'required_without:newAuthors', //for existing authors
            'newAuthors'=>'required_without:authors', //for new authors to be added to DB.
            'newAuthors.*.firstName' => 'required_without:authors|string',
            'newAuthors.*.lastName' => 'required_without:authors|string',
            'authors.*.ID' => 'required_without:newAuthors|numeric'
        ]);
        if ($validator->fails()) {
            return ["message" => "invalid request", "code"=>400];
        }
        //start transaction:
        return DB::transaction(function () use ($request) {
             //firstly, create new book:
            $bookID = $this->booksController->store($request->get("title"));
            //then, create new authors and assign them as the new book's authors:
             if ( $request->get("newAuthors") ){
                foreach ($request->get("newAuthors") as $newAuthor){
                    //make new authors and get their IDs:
                    $newAuthorID =  $this->authorsController->store($newAuthor);
                    $this->store($newAuthorID,$bookID);
                }
            }
            if ($request->get("authors") ){
                foreach ($request->get("authors") as $existingAuthor){
                    //assign the existing authors as the authors of this book:
                    $this->store($existingAuthor["ID"],$bookID);
                }
            }
            return ["message" => "books with their associated authors created successfully", "code"=>200] ;

         });

    }
    /**
     * Joins the authors and their books together, and also includes authors that do not have books assigned to them.
     * @return Illuminate\Database\Query\Builder the query.
     */
    public function index(){
        //note : authors_books.books_ID is selected to avoid same columns "ID" clashing bug.
        return DB::table('authors_books')
            ->rightJoin(Authors::TABLE_NAME, 'authors.ID', '=', 'authors_books.authors_ID')
            ->leftJoin(Books::TABLE_NAME, 'books.ID', '=', 'authors_books.books_ID')
            ->select('authors.ID', 'authors.firstName', 'authors.lastName', 'authors_books.books_ID', 'books.title');
     }
    /**
     * exports authors and their books into CSV file.
     * @return CSV file.
     */
    public function exportToCSV(){
        $query = $this->index();
        $this->export = new DBExport( $query->get(),$query->columns);
        return $this->exportUtility->exportToCSV($this->export,'authors.csv');


    }
}
