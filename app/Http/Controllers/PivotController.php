<?php

namespace App\Http\Controllers;

use App\Authors;
use App\Books;
use App\Exports\DBExport;
use App\Exports\PivotExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
    public const ID_FIELD = "relationID";

    public const AUTHORS_AND_BOOKS_EXPORT_CSV_FILENAME = 'authorsAndBooks.csv';
    public const AUTHORS_ID_FIELD =  Authors::TABLE_NAME . "_". Authors::ID_FIELD;
    public const BOOKS_ID_FIELD = Books::TABLE_NAME . "_". Books::ID_FIELD;
    public const BOOKS_CREATION_FAILED_MESSAGE = "failed to create books and their associated authors";
    public const BOOKS_CREATION_SUCCEED_MESSAGE ="books with their associated authors created successfully";
    public const NEW_AUTHORS_REQUEST = "newAuthors";
    public const EXISTING_AUTHORS_REQUEST = "existingAuthors";
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
            return  response()->json([ExportUtilityController::MESSAGE_RESPONSE_KEY => ExportUtilityController::INVALID_REQUEST_MESSAGE],
                ExportUtilityController::INVALID_REQUEST_STATUS);
        }
        //for simplicity, do an exact matching search (not case sensitive):
        return    $this->query()->where(Authors::FIRSTNAME_FIELD , '=', $request[Authors::FIRSTNAME_FIELD])
            ->where(Authors::LASTNAME_FIELD , '=', $request[Authors::FIRSTNAME_FIELD] )->get();
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
        return $this->query()->where(Books::TITLE_FIELD , '=', $request[Books::TITLE_FIELD ])->get();
    }


    /**
     * assigns an author to a book.
     */
    public function store($authorID, $bookID  )
    {
        return DB::table(PivotController::TABLE_NAME)->insertGetId([self::AUTHORS_ID_FIELD => $authorID,
            self::BOOKS_ID_FIELD => $bookID]);
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
         * at least 1 author is required (existing or new is fine)
         * we also need the book's title to create the new book
         * note: the "string" keyword implicitly eliminates empty string. */

        $validator = Validator::make($request->all(), [
            Books::TITLE_FIELD => 'required|string',
            self::EXISTING_AUTHORS_REQUEST=> 'required_without:' . self::NEW_AUTHORS_REQUEST, //for existing authors
            self::NEW_AUTHORS_REQUEST =>'required_without:' . self::EXISTING_AUTHORS_REQUEST, //for new authors to be added to DB.
            self::NEW_AUTHORS_REQUEST.'.*.' . Authors::FIRSTNAME_FIELD =>
                'required_with:'. self::NEW_AUTHORS_REQUEST .'|string',
            self::NEW_AUTHORS_REQUEST.'.*.' . Authors::LASTNAME_FIELD  =>
                'required_with:'.self::NEW_AUTHORS_REQUEST.'|string',
            self::EXISTING_AUTHORS_REQUEST . '.*.' . Authors::ID_FIELD =>
                'required_with:'.self::EXISTING_AUTHORS_REQUEST.'|numeric'
        ]);
        if ($validator->fails()) {
            return  response()->json([ExportUtilityController::MESSAGE_RESPONSE_KEY =>
                ExportUtilityController::INVALID_REQUEST_MESSAGE],
                ExportUtilityController::INVALID_REQUEST_STATUS);
        }
        //start transaction:
        return DB::transaction(function () use ($request) {
            try {
                //firstly, create new book:
                $bookID = $this->booksController->store($request->get(Books::TITLE_FIELD));
                $newAuthorsID = []; // to show that new Authors have been added.
                $relationsID = []; //to show that the authors have been linked with the book.
                //then, create new authors and assign them as the new book's authors:
                if ( $request->get(self::NEW_AUTHORS_REQUEST) ){
                    foreach ($request->get(self::NEW_AUTHORS_REQUEST) as $newAuthor){
                        //make new authors and get their IDs:
                        $newAuthorID =  $this->authorsController->store($newAuthor);
                        //store their ID to be returned later:
                        array_push($newAuthorsID,$newAuthorID);
                        //assign the to this book:
                        $relationID = $this->store($newAuthorID,$bookID);
                        //store this ID to be returned later:
                        array_push($relationsID,$relationID);
                    }
                }
                if ($request->get( self::EXISTING_AUTHORS_REQUEST) ){
                    foreach ($request->get( self::EXISTING_AUTHORS_REQUEST) as $existingAuthor){
                        //assign the existing authors as the authors of this book:
                        $relationID = $this->store($existingAuthor[Authors::ID_FIELD],$bookID);
                        //store this ID to be returned later:
                        array_push($relationsID,$relationID);
                    }
                }
                /* returns a success message, showing the book's ID, the new authors' ID,
                 * and relationID: an ID in the pivot table that connects between each author to this book.
                 */
                return  response()->json([
                    ExportUtilityController::MESSAGE_RESPONSE_KEY => self::BOOKS_CREATION_SUCCEED_MESSAGE,
                    Books::ID_FIELD => $bookID,
                    self::ID_FIELD => $relationsID,
                    Authors::ID_FIELD => $newAuthorsID],
                     ExportUtilityController::CREATED_STATUS);

             //something went wrong with the transaction, rollback
            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollBack();
                return  response()->json([
                    ExportUtilityController::MESSAGE_RESPONSE_KEY => self::BOOKS_CREATION_FAILED_MESSAGE,
                    ExportUtilityController::ERROR_RESPONSE_KEY => $e->getMessage()],
                    ExportUtilityController::INTERNAL_SERVER_ERROR_STATUS);
            } catch (\Exception $e) {
                // something went wrong elsewhere, handle gracefully
                DB::rollBack();
                return  response()->json([
                    ExportUtilityController::MESSAGE_RESPONSE_KEY => self::BOOKS_CREATION_FAILED_MESSAGE,
                    ExportUtilityController::ERROR_RESPONSE_KEY => $e->getMessage()],
                    ExportUtilityController::INTERNAL_SERVER_ERROR_STATUS);
            }
         });

    }
    /**
     * Executes a query for index() function.
     * @return \Illuminate\Database\Query\Builder, the query.
     */
    public function query(){
        //note : authors_books.books_ID is selected to avoid same columns "ID" clashing bug.
        return DB::table(self::TABLE_NAME)
            ->rightJoin(Authors::TABLE_NAME, Authors::ID_FIELD, '=', self::AUTHORS_ID_FIELD)
            ->leftJoin(Books::TABLE_NAME, Books::ID_FIELD, '=', self::BOOKS_ID_FIELD)
            ->select(Authors::ID_FIELD, Authors::FIRSTNAME_FIELD, Authors::LASTNAME_FIELD, Books::ID_FIELD,
                Books::TITLE_FIELD);
     }
    /**
     *  Gets a result from a query that joins the authors and their books together, and also includes authors that
     * do not have books assigned to them.
     * @return \Illuminate\Support\Collection,  the query result.
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
