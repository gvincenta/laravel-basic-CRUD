<?php


namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;
use App\Exports\DBExport;

/**
 * Class AuthorsTest
 * @package Tests\Feature
 * a class to test all requests directed to /api/authors/... endpoint
 */
class AuthorsTest extends TestCase
{

    //cleans up the DB before and after testing:
    use RefreshDatabase;
    // use without the need to send CSRF tokens to simplify http requests like post, put and delete.

    use WithoutMiddleware;

    private $utilityTest,$title,$authors;

    /**
     * AuthorsTest constructor.
     * initiates helper class instance and also some sample data to be used in the test.
     */
    public function __construct()
    {
        parent::__construct();
        $this->utilityTest = new UtilityTest();
        $this->authors = [
            ['firstName' => 'Midoriya', 'lastName' => 'Zoldyck'],
            ["firstName" => "Updated", 'lastName' => "Wrong"],
            ['firstName' => 'Updated', 'lastName' =>'Updated']
        ];
        $this->title = "Change Author Name";

    }
    /**  @test updating an author's name in database. */
    public function changeAuthorName()
    {
        //try to update with valid request but DB is empty (in other words, updating with invalid ID):
        $this->changeAuthorNameWithInvalidID();
        //create a book with an author:

        $createResponse = $this->utilityTest->createABook($this->title, [$this->authors[0]] );
        //then, update an author it through its ID:
        $this->changeAuthorNameWithValidRequest($createResponse['newAuthorsID'][0],$this->authors[0]);
        //update with empty body:
        $this->changeAuthorNameWithEmptyRequest();
        //update with invalid firstName and lastName:
        $this->changeAuthorNameWithInvalidName($createResponse['newAuthorsID'][0]);
    }

    /** Changes an author's name with valid ID, firstName, and lastName.
     * @param $id, ID of an author that exists in the database.
     * @param $current, the author's current firstName and lastName.
     */
    public function changeAuthorNameWithValidRequest($id, $current)
    {
        $updateResponse = $this->json('PUT','/api/authors',['ID'=>$id,
            'firstName' => 'Updated',
            'lastName' =>'Updated'
        ]);
        //checks that a proper response is returned:
        $this->utilityTest->checkOKResponseWithCustomMessage($updateResponse, "changing name succeed");
        //check that changes are successfully made in the database:
        $this->assertDatabaseHas('authors', $this->authors[2]);
        $this->assertDatabaseMissing('authors', $current);
    }

    /** requests to change an author's name with no request body. */
    public function changeAuthorNameWithEmptyRequest()
    {
        $updateResponse =  $this->utilityTest->sendEmptyRequest('/api/authors','PUT');
        $this->utilityTest->checkInvalidResponse($updateResponse);
    }

    /** Changes an author's name with invalid firstName and lastName.
     * @param $id, ID of an author that exists in the database.
     */
    public function changeAuthorNameWithInvalidName($id)
    {
        $invalidName = ['firstName' => 123, 'lastName' => 456];
        $updateResponse = $this->json('PUT','/api/authors',['ID'=>$id,
            'firstName' => 123, 'lastName' => 456]);
        $this->utilityTest->checkInvalidResponse($updateResponse);
        $this->assertDatabaseMissing('authors', $invalidName);
    }
    /** Changes an author's name with invalid ID.
     * @param $id, ID of an author that does not exist in the database.
     */
    public function changeAuthorNameWithInvalidID()
    {
        $updateResponse = $this->json('PUT','/api/authors',['ID'=> 2,
            'firstName' => "Updated", 'lastName' => "Wrong"]);
        $this->utilityTest->checkOKResponseWithCustomMessage($updateResponse, "changing name failed");
        $this->assertDatabaseMissing('authors', $this->authors[1]);
    }

    /**  @test  search for a book by its author. */
    public function searchByAuthor()
    {
        $this->utilityTest->searchTestFacade('/api/authors/with-filter');
    }
    /** @test requests for a authors (only) content  exported as a CSV and then check its content.   */
    public function exportAuthorsToCSV()
    {
        $this->utilityTest->exportToCSV( ['ID','firstName','lastName'] ,'/api/authors/export/CSV','authors.csv');
    }
    /**  @test requests for a books and authors content  exported as a CSV and then check its content. */
    public function exportAuthorsAndBooksToCSV()
    {
        $this->utilityTest->exportToCSV( ['ID','firstName','lastName','books_ID','title'] ,'/api/authors/export/CSV/with-books',
            'authorsAndBooks.csv');
    }
    /**  @test  requests for a authors (only) content  exported as XML and then check its content. */
    public function exportAuthorsToXML(){
        $this->utilityTest->exportToXML( [['ID','firstName','lastName']],'/api/authors/export/XML',"authors");

    }

    /**  @test requests for authors and books  content  exported as XML and then check its content. */
    public function exportAuthorsAndBooksToXML(){
        $this->utilityTest->exportToXML( [['ID','firstName','lastName'], ['ID', 'title']],'/api/authors/export/XML/with-books',
            "authors","books");
    }
}

