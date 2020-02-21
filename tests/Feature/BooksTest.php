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

    public function __construct()
    {
        parent::__construct();
        $this->utilityTest = new UtilityTest();
        $this->authors = [
            ['firstName' => 'Midoriya', 'lastName' => 'Zoldyck'],
            ['firstName' => 'Michael', 'lastName' => 'AJ']
        ];
        $this->title = 'Alpha Beta';
        //for testing adding new book with existing authors:
        $this->authorIDs = [];
    }
    public function getEmptyBooksAndAuthors(){
        $response = $this->get('api/books');
        $this->utilityTest->checkEmptyJsonContent($response);
    }

    /**
     * @test tests a get request for Books And Authors table.
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
    /**
     * @test search for a book by its title.
     */
    public function searchByTitle()
    {
        $this->utilityTest->searchTestFacade('/api/books/with-filter');

    }
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
    //check that the relationships between a book and its authors have been created in DB:
    public function validateDBRelation($bookID, $authorIDs, $relationIDs){
        $lenAuthorIDs = count($authorIDs);
        $lenRelationIDs = count($relationIDs);
        $this->assertTrue($lenAuthorIDs == $lenRelationIDs);
        for($i = 0; $i < $lenAuthorIDs; $i++){
            $this->assertDatabaseHas('authors_books', ['authors_ID' =>$authorIDs[$i]["ID"], 'books_ID' =>$bookID,
                'ID' =>$relationIDs[$i] ]);
        }

    }
    public function addABookWithAuthorsWithInvalidRequest(){
        //invalid input #1: non-string titles
        $response = $this->utilityTest->createABook(123, $this->authors );
        $this->utilityTest->checkInvalidRequestResponse($response);
        $this->assertDatabaseMissing ('books', ['title'=>123]);

        //invalid input #2: no authors
        $response = $this->utilityTest->createABook("No Authors");
        $this->utilityTest->checkInvalidRequestResponse($response);
        $this->assertDatabaseMissing('books', ['title'=>'No Authors']);
    }
    /**
     * @test adding a book and assigns authors to it.
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

    public function checkIDType($IDarray){
        foreach ($IDarray as $ID){
            $this->assertTrue(gettype($ID) == "integer");
        }
    }
    /**
     * @test deleting a book in the database.
     */
    public function deleteABook()
    {

        $newAuthor = ['firstName' => 'Midoriya', 'lastName' => 'Zoldyck'];

        //firstly, must create a book. testing for creating book's responses is done in depth on addABookWithAuthor()
        $createResponse = $this->json('POST','/api/books',['title'=>'To Be Deleted',
            'newAuthors' => [
                $newAuthor
            ]
        ]);
        //test sufficiently to be able to delete the book:
        $createResponse->assertStatus(201);
        $this->assertTrue(gettype($createResponse['bookID']) == "integer");
        $this->assertDatabaseHas('books', ['title'=>'To Be Deleted']);

        //delete with an invalid request:
        //BUG: somehow it allows integers in string format.
//        $invalidID = (string)$createResponse['bookID'];
//        $this->assertTrue(gettype($invalidID) == "string");
//        $deleteResponse = $this->json('DELETE','/api/books',[ 'ID'=> $invalidID]);
//        $deleteResponse->assertStatus(400);
//        $this->assertTrue($deleteResponse['message'] == "invalid request");


        //then, delete it through its ID:
        $deleteResponse = $this->json('DELETE','/api/books',['ID'=> $createResponse['bookID']]);
        $deleteResponse->assertStatus(200);
        $this->assertTrue($deleteResponse['message'] == "deleting a book succeed");
        $this->assertDatabaseMissing('books', ['title'=>'To Be Deleted','ID'=> $createResponse['bookID']]);

        //try to delete a non existing book:
        $deleteResponse = $this->json('DELETE','/api/books',['ID'=> $createResponse['bookID']]);
        $deleteResponse->assertStatus(200);
        $this->assertTrue($deleteResponse['message'] == "deleting a book failed");

        //delete with an empty request:
        $deleteResponse = $this->json('DELETE','/api/books',[ ]);
        $deleteResponse->assertStatus(400);
        $this->assertTrue($deleteResponse['message'] == "invalid request");

        //delete with a null ID request:
        $deleteResponse = $this->json('DELETE','/api/books',[ 'ID'=> null]);
        $deleteResponse->assertStatus(400);
        $this->assertTrue($deleteResponse['message'] == "invalid request");
    }
    /**
     * @test  exporting books (only) to csv.
     */
    public function exportBooksToCSV()
    {
        $this->utilityTest->exportToCSV( ['ID','title'] ,'/api/books/export/CSV','books.csv');
    }
    /**
     * @test  exporting books (only) to XMl.
     */
    public function exportBooksToXML(){
        $this->utilityTest->exportToXML([['ID', 'title']  ], '/api/books/export/XML',"books");
    }

    /**
     * @test  exporting books and authors to XMl.
     */
    public function exportsBooksAndAuthorsToXML(){
        $this->utilityTest->exportToXML( [['ID', 'title'], ['ID','firstName','lastName'] ],'/api/books/export/XML/with-authors',
            "books","authors");
    }







}
