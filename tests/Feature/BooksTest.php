<?php

namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Books;
use App\Authors;
use App\Http\Controllers\PivotController;
use App\Http\Controllers\BooksController;
use App\Http\Controllers\UtilityController;

/**
 * Class BooksTest
 * @package Tests\Feature
 * a class to test all requests directed to /api/books/... endpoint
 */
class BooksTest extends TestCase
{
    //cleans up the DB before and after testing:
    use RefreshDatabase;
    // use without the need to send CSRF tokens to simplify http requests like post, put and delete.
    use WithoutMiddleware;
    private $utilityTest,$authors,$title,$authorIDs;

    /** populate some data for testing: */
    public function __construct()
    {
        parent::__construct();

        $this->utilityTest = new UtilityTest();
        $this->authors = [
            [Authors::FIRSTNAME_FIELD  => 'Kimberly', Authors::LASTNAME_FIELD  => 'Winson'],
            [Authors::FIRSTNAME_FIELD  => 'Michael', Authors::LASTNAME_FIELD  => 'AJ'],
            [Authors::FIRSTNAME_FIELD  => 'Ten', Authors::LASTNAME_FIELD  => 'Shi'],
            [Authors::FIRSTNAME_FIELD  => 'Net', Authors::LASTNAME_FIELD  => 'To'],
            [Authors::FIRSTNAME_FIELD  => 'Hi', Authors::LASTNAME_FIELD  => 'Hello'],
            [Authors::FIRSTNAME_FIELD  => 'Kukira', Authors::LASTNAME_FIELD  => 'Ada']
        ];
        $this->titles = ['Alpha','Beta','Gamma',"Exist","Auth","Radius" ];
        $this->invalidTitles = [1,2];

        //for testing adding new book with existing authors, store the authors' ID:
        $this->authorIDs = [];
    }

    ################################## Get Books and Authors table: ###############################################
    /**
     * @test tests a get request from api/books for Books And Authors table.
     * 1. tests when the backend tables are empty.
     * 2. create a book with an author, and then try to get them returned via this get request.
     */
    public function getBooksAndAuthors()
    {
        //expect an empty json when DB is empty:
        $this->getEmptyBooksAndAuthors();

        //then, create a book with an author:
        $createResponse = $this->utilityTest->createABook($this->titles[0], [$this->authors[0]] );

        //now test that they are returned when doing a get request, and nothing else is returned besides that:
        $response = $this->get('api/books');
        $response
            ->assertStatus(200)
            ->assertExactJson( [[
                Authors::ID_FIELD=> $createResponse[Authors::ID_FIELD][0],
                Authors::FIRSTNAME_FIELD=> $this->authors[0][Authors::FIRSTNAME_FIELD],
                Authors::LASTNAME_FIELD=> $this->authors[0][Authors::LASTNAME_FIELD],
                Books::TITLE_FIELD=> $this->titles[0],
                Books::ID_FIELD=> $createResponse[Books::ID_FIELD]
            ] ]);
    }

    /** expects an empty json to be returned when database's Books and Authors tables are empty. */
    public function getEmptyBooksAndAuthors(){
        $response = $this->get('api/books');
        $this->utilityTest->checkEmptyJsonContent($response);
    }

    ################################Search By Titles##################################################

    /**  @test search for a book by its title. */
    public function searchByTitle()
    {
        $this->utilityTest->searchTestFacade('/api/books/with-filter');
    }
    ################################Add A Book With Authors ##################################################
    /**
     * @test adding a book and assigns authors to it.
     * 1. adds a book with new (non-existing) authors to the database.
     * 2. adds a book with new authors.
     * 3. adds a book with invalid request body.
     * 4. adds a book with existing authors in the database.
     */
    public function addABookWithAuthors()
    {
        //add a book with invalid ID (commented out, as it affects RefreshDatabase functionality):
        $this->addABookWithInvalidAuthorID();
        //add with new authors:
        $this->addABookWithNewAuthors();
        //testing with invalid inputs:
        $this->addABookWithAuthorsWithInvalidRequest();
        //valid input #2: add with existing authors:
        $this->addABookWithExistingAuthors();
    }

    /**  @test Sends a request to backend to make a new book with a wrong authorID.  */
    public function addABookWithInvalidAuthorID(){
        $response = $this->utilityTest->createABook($this->titles[1], [ $this->authors[1] ],
            [ [Authors::ID_FIELD =>2] ] );

        //expects DB to rollback with internal server error status:
        $response->assertStatus(UtilityController::INTERNAL_SERVER_ERROR_STATUS);
        $this->assertDatabaseMissing(Books::TABLE_NAME, [Books::TITLE_FIELD => $this->titles[1]]);
        $this->assertDatabaseMissing(Authors::TABLE_NAME,$this->authors[1]);

    }
    /** Sends a request to backend to make a new book with a new (non-existing) authors.  */
    public function addABookWithNewAuthors(){

        $response = $this->utilityTest->createABook($this->titles[2], [$this->authors[2],$this->authors[3]] );
        /*note that testing the response of this API in the following lines below are very crucial, as most of other
        tests functions  need to initially make a book with this endpoint. */

        //make sure status is correct:
        $response->assertStatus(UtilityController::CREATED_STATUS);

        //make sure response returned properly:
        $this->assertTrue(count($response[Authors::ID_FIELD]) == 2); // 2 authors created in DB with their ID returned.
        $this->assertTrue(count($response[PivotController::ID_FIELD]) == 2); // both authors assigned to the book in pivot table.

        //check that all IDs returned are in integer type:
        $this->checkIDType([$response[Books::ID_FIELD]]);
        $this->checkIDType($response[PivotController::ID_FIELD]);


        foreach ($response[Authors::ID_FIELD] as $newAuthorID){
            $this->assertTrue(gettype($newAuthorID) == "integer");
            //store authors' ID for next test:
            array_push($this->authorIDs, [Authors::ID_FIELD => $newAuthorID]);
        }

        //check that they exist in DB:
        $this->assertDatabaseHas(Books::TABLE_NAME, [Books::TITLE_FIELD => $this->titles[2]]);
        $this->assertDatabaseHas(Authors::TABLE_NAME, $this->authors[2]);
        $this->assertDatabaseHas(Authors::TABLE_NAME, $this->authors[3]);
        //check that the relationship is created in DB:
       $this->validateDBRelation($response[Books::ID_FIELD],$this->authorIDs,$response[PivotController::ID_FIELD]);

    }

    /** check that the relationships between a book and its authors have been created in DB.
     * @param integer $bookID,  the expected book's ID
     * @param array $authorIDs, the expected author(s)
     * @param array $relationIDs, the ID in the pivot table that relates the book to a certain author.
     */
    public function validateDBRelation($bookID, $authorIDs, $relationIDs){
        $lenAuthorIDs = count($authorIDs);
        $lenRelationIDs = count($relationIDs);
        $this->assertTrue($lenAuthorIDs == $lenRelationIDs);
        for($i = 0; $i < $lenAuthorIDs; $i++){
            $this->assertDatabaseHas(PivotController::TABLE_NAME , [
                PivotController::AUTHORS_ID_FIELD =>$authorIDs[$i][Authors::ID_FIELD],
                PivotController::BOOKS_ID_FIELD =>$bookID ]);
        }

    }

    /** tests the adding a book functionality with an invalid request.
     * 1. numeric titles
     * 2. books without any authors
     */
    public function addABookWithAuthorsWithInvalidRequest(){
        //invalid input #1: non-string titles
        $response = $this->utilityTest->createABook($this->invalidTitles[0], [$this->authors[4]] );
        $this->utilityTest->checkInvalidResponse($response);
        $this->assertDatabaseMissing (Books::TABLE_NAME, [Books::TITLE_FIELD=>$this->invalidTitles[0]]);

        //invalid input #2: no authors
        $response = $this->utilityTest->createABook($this->titles[3]);
        $this->utilityTest->checkInvalidResponse($response);
        $this->assertDatabaseMissing(Books::TABLE_NAME, [Books::TITLE_FIELD=>$this->titles[3]]);
    }

    /** adding a book functionality with existing authors in the database. */
    public function addABookWithExistingAuthors(){

        $response = $this->utilityTest->createABook( $this->titles[4], [], $this->authorIDs );
        //make sure status is correct:
        $response->assertStatus(UtilityController::CREATED_STATUS);
        $this->assertTrue(count($response[PivotController::ID_FIELD]) == 2); // both authors assigned to the book in pivot table.

        $this->checkIDType([$response[Books::ID_FIELD]]);
        $this->checkIDType($response[PivotController::ID_FIELD]);
        //check that they exist in DB:
        $this->assertDatabaseHas(Books::TABLE_NAME, [ Books::TITLE_FIELD => $this->titles[4]]);
        //check that the relationship is created in DB:
        $this->validateDBRelation($response[Books::ID_FIELD],$this->authorIDs,$response[PivotController::ID_FIELD]);
    }

    /** make sure all IDs are in integer type:
     * @param $IDarray an array of IDs given from backend's response.
     */
    public function checkIDType($IDarray){
        foreach ($IDarray as $ID){
            $this->assertTrue(gettype($ID) == "integer");
        }
    }
    ################################## Delete A Book: ###############################################
    /**
     * @test deleting a book in the database.
     * 1. deleting a book with an invalid ID
     * 2. deleting a book with a valid request
     * 3. deleting a book without specifying an ID
     */
    public function deleteABook()
    {
        //try to delete with valid request but DB is empty (in other words, deleting with invalid ID):
        $this->deleteABookWithInvalidID();
        //delete with a valid request:
        $this->deleteABookWithValidRequest();
        //delete with an (invalid) empty request (i.e. not specifying the book's ID):
        $this->deleteABookWithEmptyResponse();
    }

    /** requests to delete a book without specifying its ID.  */
    public function deleteABookWithEmptyResponse(){
        $deleteResponse = $this->json('DELETE','/api/books',[]);
        $this->utilityTest->checkInvalidResponse($deleteResponse);
    }

    /** requests to delete a book with an ID that doesn't exist in the books table. */
    public function deleteABookWithInvalidID(){
        $deleteResponse = $this->json('DELETE','/api/books',[Books::ID_FIELD => 0]);
        $this->utilityTest->checkOKResponseWithCustomMessage($deleteResponse,
            BooksController::DELETE_A_BOOK_FAILED_MESSAGE);
    }
    /** requests to delete a book with an ID that exists in the books table. */
    public function deleteABookWithValidRequest(){
        //firstly, must create a book.
        $createResponse = $this->utilityTest->createABook($this->titles[5], [$this->authors[5]] );
        //then delete it:
        $deleteResponse = $this->json('DELETE','/api/books',[Books::ID_FIELD => $createResponse[Books::ID_FIELD]]);
        $this->utilityTest->checkOKResponseWithCustomMessage($deleteResponse,
            BooksController::DELETE_A_BOOK_SUCCEED_MESSAGE);
        //make sure book deleted:
        $this->assertDatabaseMissing(Books::TABLE_NAME, [Books::TITLE_FIELD=> $this->titles[5],
            Books::ID_FIELD => $createResponse[Books::ID_FIELD] ]);
        //make sure book's relationship(s) also deleted:
        $this->assertDatabaseMissing(PivotController::TABLE_NAME, [PivotController::ID_FIELD=>
            $createResponse[PivotController::ID_FIELD][0]  ]);
    }
    ################################## Export to CSV and XML: ###############################################
    /**  @test  requests for a books (only) content  exported as a CSV and then check its content. */
    public function exportBooksToCSV()
    {
        $this->utilityTest->exportToCSV( [Books::ID_FIELD,Books::TITLE_FIELD] ,'/api/books/export/CSV',
            BooksController::BOOKS_EXPORT_CSV_FILENAME);
    }
    /** @test  requests for a books (only) content to be exported as a XML and then check its content.*/
    public function exportBooksToXML(){
        $this->utilityTest->exportToXML([[Books::ID_FIELD, Books::TITLE_FIELD]  ], '/api/books/export/XML',
            Books::TABLE_NAME);
    }

    /**  @test requests for a books and authors content to be exported as a XML and then check its content.   */
    public function exportsBooksAndAuthorsToXML(){
        $this->utilityTest->exportToXML( [[Books::ID_FIELD, Books::TITLE_FIELD],
            [Authors::ID_FIELD, Authors::FIRSTNAME_FIELD, Authors::LASTNAME_FIELD] ],
            '/api/books/export/XML/with-authors', Books::TABLE_NAME, Authors::TABLE_NAME);
    }


}
