<?php

namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
//a class to test most of the /api/books endpoint
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
            ['firstName' => 'Midoriya', 'lastName' => 'Zoldyck'],
            ['firstName' => 'Michael', 'lastName' => 'AJ']
        ];
        $this->title = 'Alpha Beta';
        //for testing adding new book with existing authors, store the authors' ID:
        $this->authorIDs = [];
    }

    /** expects an empty json to be returned when backend Books and Authors tables are empty. */
    public function getEmptyBooksAndAuthors(){
        $response = $this->get('api/books');
        $this->utilityTest->checkEmptyJsonContent($response);
    }

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
        $createResponse = $this->utilityTest->createABook($this->title, [$this->authors[0]] );

        //now test that they are returned when doing a get request, and nothing else is returned besides that:
        $response = $this->get('api/books');
        $response
            ->assertStatus(200)
            ->assertExactJson( [[
                "ID"=> $createResponse['newAuthorsID'][0],
                "firstName"=> $this->authors[0]['firstName'],
                "lastName"=> $this->authors[0]['lastName'],
                "books_ID"=> $createResponse["bookID"] ,
                "title"=> $this->title
            ] ]);
    }

    /**  @test search for a book by its title. */
    public function searchByTitle()
    {
        $this->utilityTest->searchTestFacade('/api/books/with-filter');

    }
    /**
     * @test adding a book and assigns authors to it.
     * 1. adds a book with new (non-existing) authors to the database.
     * 2. adds a book with invalid request body.
     * 3. adds a book with existing authors in the database.
     */
    public function addABookWithAuthors()
    {
        //add with new authors:
        $this->addABookWithNewAuthors();
        //testing with invalid inputs:
        $this->addABookWithAuthorsWithInvalidRequest();
        //valid input #2: add with existing authors:
        $this->addABookWithExistingAuthors();

    }

    /** Sends a request to backend to make a new book with a new (non-existing) authors.  */
    public function addABookWithNewAuthors(){

        $response = $this->utilityTest->createABook($this->title, $this->authors );
        /*note that testing the response of this API in the following lines below are very crucial, as most of other
        tests functions  need to initially make a book with this endpoint. */

        //make sure status is correct:
        $response->assertStatus(201);

        //make sure response returned properly:
        $this->assertTrue(count($response['newAuthorsID']) == 2); // 2 authors created in DB with their ID returned.
        $this->assertTrue(count($response['relationsID']) == 2); // both authors assigned to the book in pivot table.

        //check that all IDs returned are in integer type:
        $this->assertTrue(gettype($response['bookID']) == "integer");
        $this->checkIDType([$response['bookID']]);
        $this->checkIDType($response['relationsID']);

        foreach ($response['newAuthorsID'] as $newAuthorID){
            $this->assertTrue(gettype($newAuthorID) == "integer");
            //store authors' ID for next test:
            array_push($this->authorIDs,["ID" =>$newAuthorID]);
        }

        //check that they exist in DB:
        $this->assertDatabaseHas('books', ['title'=> $this->title]);
        $this->assertDatabaseHas('authors', $this->authors[0]);
        $this->assertDatabaseHas('authors', $this->authors[1]);
        //check that the relationship is created in DB:
       $this->validateDBRelation($response['bookID'],$this->authorIDs,$response['relationsID']);

    }

    /** check that the relationships between a book and its authors have been created in DB.
     * @param $bookID the expected book's ID
     * @param $authorIDs the expected author(s)
     * @param $relationIDs the ID in the pivot table that relates the book to a certain author.
     */
    public function validateDBRelation($bookID, $authorIDs, $relationIDs){
        $lenAuthorIDs = count($authorIDs);
        $lenRelationIDs = count($relationIDs);
        $this->assertTrue($lenAuthorIDs == $lenRelationIDs);
        for($i = 0; $i < $lenAuthorIDs; $i++){
            $this->assertDatabaseHas('authors_books', ['authors_ID' =>$authorIDs[$i]["ID"], 'books_ID' =>$bookID ]);
        }

    }

    /** tests the adding a book functionality with an invalid request.
     * 1. numeric titles
     * 2. books without any authors
     */
    public function addABookWithAuthorsWithInvalidRequest(){
        //invalid input #1: non-string titles
        $response = $this->utilityTest->createABook(123, $this->authors );
        $this->utilityTest->checkInvalidResponse($response);
        $this->assertDatabaseMissing ('books', ['title'=>123]);

        //invalid input #2: no authors
        $response = $this->utilityTest->createABook("No Authors");
        $this->utilityTest->checkInvalidResponse($response);
        $this->assertDatabaseMissing('books', ['title'=>'No Authors']);
    }

    /**tests the adding a book functionality with existing authors in the database. */
    public function addABookWithExistingAuthors(){
        $title = "Never Been Added Yet";
        $response = $this->utilityTest->createABook( $title, [], $this->authorIDs );
        //make sure status is correct:
        $response->assertStatus(201);
        $this->assertTrue(count($response['relationsID']) == 2); // both authors assigned to the book in pivot table.

        $this->checkIDType([$response['bookID']]);
        $this->checkIDType($response['relationsID']);
        //check that they exist in DB:
        $this->assertDatabaseHas('books', ['title'=> $this->title]);
        //check that the relationship is created in DB:
        $this->validateDBRelation($response['bookID'],$this->authorIDs,$response['relationsID']);



    }
    //

    /** make sure all IDs are in integer type:
     * @param $IDarray an array of IDs given from backend's response.
     */
    public function checkIDType($IDarray){
        foreach ($IDarray as $ID){
            $this->assertTrue(gettype($ID) == "integer");
        }
    }
    /**
     * @test deleting a book in the database.
     * 1. deleting a book with an invalid ID
     * 2. deleting a book with a valid request
     * 3. deleting a book without specifying an ID
     */
    public function deleteABook()
    {
        //try to update with valid request but DB is empty (in other words, updating with invalid ID):
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
        $deleteResponse = $this->json('DELETE','/api/books',['ID'=> 0]);
        $this->utilityTest->checkOKResponseWithCustomMessage($deleteResponse,"deleting a book failed");
    }
    /** requests to delete a book with an ID that exists in the books table. */
    public function deleteABookWithValidRequest(){
        //firstly, must create a book.
        $createResponse = $this->utilityTest->createABook($this->title, [$this->authors[0]] );
        //then delete it:
        $deleteResponse = $this->json('DELETE','/api/books',['ID'=> $createResponse['bookID']]);
        $this->utilityTest->checkOKResponseWithCustomMessage($deleteResponse,"deleting a book succeed");

        $this->assertDatabaseMissing('books', ['title'=>'To Be Deleted','ID'=> $createResponse['bookID']]);
    }

    /**  @test  requests for a books (only) content  exported as a CSV and then check its content. */
    public function exportBooksToCSV()
    {
        $this->utilityTest->exportToCSV( ['ID','title'] ,'/api/books/export/CSV','books.csv');
    }
    /** @test  requests for a books (only) content to be exported as a XML and then check its content.*/
    public function exportBooksToXML(){
        $this->utilityTest->exportToXML([['ID', 'title']  ], '/api/books/export/XML',"books");
    }

    /**  @test requests for a books and authors content to be exported as a XML and then check its content.   */
    public function exportsBooksAndAuthorsToXML(){
        $this->utilityTest->exportToXML( [['ID', 'title'], ['ID','firstName','lastName'] ],'/api/books/export/XML/with-authors',
            "books","authors");
    }
}
