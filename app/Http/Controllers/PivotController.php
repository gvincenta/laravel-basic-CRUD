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
    public const AUTHORS_AND_BOOKS_EXPORT_CSV_FILENAME = 'authorsAndBooks.csv';

    private $exportUtility, $export,$booksController,$authorsController;

    public function __construct()
    {
        $this->exportUtility = new ExportUtilityController();
        $this->booksController = new BooksController();
        $this->authorsController = new AuthorsController();

    }
    /**
     * handles searching for a book through its author.
     * @param Illuminate\Http\Request $request, containing the   firstName and lastName.
     * @return  Illuminate\Http\Response  the book(s) according to author, if request is valid.
     * @return Illuminate\Http\Response  invalid request message, if request is not valid.
     */
    public function showByAuthor(Request $request){
        //for search by authors, we need their firstName and lastName.
        $validator = Validator::make($request->all(), [
            Authors::FIRSTNAME_FIELD => 'required|string',
            Authors::LASTNAME_FIELD =>'required|string'
        ]);
        if ($validator->fails()) {
            return  response()->json(['message' => ExportUtilityController::INVALID_REQUEST_MESSAGE],
                ExportUtilityController::INVALID_REQUEST_STATUS);
        }
        //for simplicity, do an exact matching search (not case sensitive):
        return    $this->query()->where('authors.firstName' , '=', $request['firstName'])
            ->where('authors.lastName' , '=', $request['lastName'] )->get();
    }
    /**
     * handles searching for a book through its title.
     * @param Illuminate\Http\Request $request, containing the title.
     * @return  Illuminate\Http\Response  the book according to title, if request is valid.
     * @return Illuminate\Http\Response  invalid request message, if request is not valid.
     */
    public function showByTitle(Request $request){
        //for search by title, we need title only.
        $validator = Validator::make($request->all(), [
            Books::TITLE_FIELD => 'required|string'
        ]);
        if ($validator->fails()) {
            return  response()->json(['message' => ExportUtilityController::INVALID_REQUEST_MESSAGE],
                ExportUtilityController::INVALID_REQUEST_STATUS);
        }
        //for simplicity, do an exact matching search (not case sensitive):
        return    $this->query()->where('books.title' , '=', $request['title'])->get();
    }


    /**
     * assigns an author to a book.
     */
    public function store($authorID, $bookID  )
    {
        return DB::table(PivotController::TABLE_NAME)->insertGetId(["authors_authorID" => $authorID,
            "books_bookID" => $bookID]);
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
            'authors.*.authorID' => 'required_without:newAuthors|numeric'
        ]);
        if ($validator->fails()) {
            return  response()->json(['message' => ExportUtilityController::INVALID_REQUEST_MESSAGE],
                ExportUtilityController::INVALID_REQUEST_STATUS);
        }
        //start transaction:
        //BUG: how to rollback ?
        return DB::transaction(function () use ($request) {
            //carry out transaction
            try {

                //firstly, create new book:
                $bookID = $this->booksController->store($request->get("title"));
                $newAuthorsID = []; // to show that new Authors have been added.
                $relationsID = []; //to show that the authors have been linked with the book.
                //then, create new authors and assign them as the new book's authors:
                if ( $request->get("newAuthors") ){
                    foreach ($request->get("newAuthors") as $newAuthor){
                        //make new authors and get their IDs:
                        $newAuthorID =  $this->authorsController->store($newAuthor);
                        array_push($newAuthorsID,$newAuthorID);
                        $relationID = $this->store($newAuthorID,$bookID);
                        array_push($relationsID,$relationID);
                    }
                }
                if ($request->get("authors") ){
                    foreach ($request->get("authors") as $existingAuthor){
                        //assign the existing authors as the authors of this book:

                        $relationID = $this->store($existingAuthor[Authors::ID_FIELD],$bookID);
                        array_push($relationsID,$relationID);
                    }
                }
                //returns a success message, showing the book's ID, the new authors' ID,
                //and relationID: an ID in the pivot table that connects between each author to this book.
                return  response()->json([
                    'message' => "books with their associated authors created successfully",
                    Books::ID_FIELD => $bookID,
                    'relationsID' => $relationsID,
                    'newAuthorsID' => $newAuthorsID],
                    201);

             //something went wrong with the transaction, rollback
            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollBack();
                return  response()->json([
                    'message' => "failed to create books and their associated authors",
                    'error'=>$e], 500);
            } catch (\Exception $e) {
                // something went wrong elsewhere, handle gracefully
                DB::rollBack();
                return  response()->json([
                    'message' => "failed to create books and their associated authors",
                    'error'=>$e], 500);

            }
         });

    }
    /**
     * Executes a query for index() function.
     * @return Illuminate\Database\Query\Builder the query.
     */
    public function query(){
        //note : authors_books.books_ID is selected to avoid same columns "ID" clashing bug.
        return DB::table('authors_books')
            ->rightJoin(Authors::TABLE_NAME, 'authors.authorID', '=', 'authors_books.authors_authorID')
            ->leftJoin(Books::TABLE_NAME, 'books.bookID', '=', 'authors_books.books_bookID')
            ->select('authors.authorID', 'authors.firstName', 'authors.lastName', 'books.bookID',
                'books.title');
     }
    /**
     *  Gets a result from a query that joins the authors and their books together, and also includes authors that
     * do not have books assigned to them.
     * @return Illuminate\Support\Collection the query result.
     */
     public function index(){
        return $this->query()->get();
     }
    /**
     * exports authors and their books into CSV file.
     * @return CSV file.
     */
    public function exportToCSV(){
        $query = $this->query();
        $this->export = new DBExport( $query->get(),$query->columns);
        return $this->exportUtility->exportToCSV($this->export,self::AUTHORS_AND_BOOKS_EXPORT_CSV_FILENAME);
    }
}
