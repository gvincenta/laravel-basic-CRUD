<?php


namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;
use App\Exports\DBExport;


//a class to test most of the /api/authors endpoint
class AuthorsTest extends TestCase
{

    //cleans up the DB before and after testing:
    use RefreshDatabase;
    // use without the need to send CSRF tokens to simplify http requests like post, put and delete.
    use WithoutMiddleware;
    private $utilityTest;

    public function __construct()
    {
        parent::__construct();
        $this->utilityTest = new UtilityTest();
    }

    public function changeAuthorNameWithValidRequest($id, $old)
    {
        $updated = ['firstName' => 'Updated', 'lastName' =>'Updated'];
        $updateResponse = $this->json('PUT','/api/authors',['ID'=>$id,
            'firstName' => 'Updated',
            'lastName' =>'Updated'
        ]);
        $this->utilityTest->checkOKResponseWithCustomMessage($updateResponse, "changing name succeed");

        $this->assertDatabaseHas('authors', $updated);
        $this->assertDatabaseMissing('authors', $old);
    }
    public function changeAuthorNameWithEmptyRequest()
    {
        $updateResponse =  $this->utilityTest->sendEmptyRequest('/api/authors','PUT');
        $this->utilityTest->checkInvalidResponse($updateResponse);
    }
    public function changeAuthorNameWithInvalidName($id)
    {
        $invalidName = ['firstName' => 123, 'lastName' => 456];
        $updateResponse = $this->json('PUT','/api/authors',['ID'=>$id,
            'firstName' => 123, 'lastName' => 456]);
        $this->utilityTest->checkInvalidResponse($updateResponse);
        $this->assertDatabaseMissing('authors', $invalidName);
    }

    public function changeAuthorNameWithInvalidID()
    {
        $update = ["firstName" => "Updated", 'lastName' => "Wrong"];
        $updateResponse = $this->json('PUT','/api/authors',['ID'=> 2,
            'firstName' => "Updated", 'lastName' => "Wrong"]);
        $this->utilityTest->checkOKResponseWithCustomMessage($updateResponse, "changing name failed");
        $this->assertDatabaseMissing('authors', $update);
    }
    /**
     * @test updating an author's name in database.
     */
    public function changeAuthorName()
    {
        //try to update with valid request but DB is empty (in other words, updating with invalid ID):
        $this->changeAuthorNameWithInvalidID();
        //create a book with an author:
        $newAuthor = ['firstName' => 'Midoriya', 'lastName' => 'Zoldyck'];
        $title = "Change Author Name";
        $createResponse = $this->utilityTest->createABook($title, [$newAuthor] );
        //then, update an author it through its ID:
        $this->changeAuthorNameWithValidRequest($createResponse['newAuthorsID'][0],$newAuthor);
        //update with empty body:
        $this->changeAuthorNameWithEmptyRequest();
        //update with invalid firstName and lastName:
        $this->changeAuthorNameWithInvalidName($createResponse['newAuthorsID'][0]);
    }
    /**
     * @test  search for a book by its author.
     */
    public function searchByAuthor()
    {

        $this->utilityTest->searchTestFacade('/api/authors/with-filter');

    }
    /**
     * @test  exporting authors (only) to csv.
     */
    public function exportAuthorsToCSV()
    {
        $this->utilityTest->exportToCSV( ['ID','firstName','lastName'] ,'/api/authors/export/CSV','authors.csv');
    }
    /**
     * @test  exporting authors and books to csv.
     */
    public function exportAuthorsAndBooksToCSV()
    {
        $this->utilityTest->exportToCSV( ['ID','firstName','lastName','books_ID','title'] ,'/api/authors/export/CSV/with-books',
            'authorsAndBooks.csv');
    }
    /**
     * @test  exporting authors (only) to XMl.
     */
    public function exportAuthorsToXML(){
        $this->utilityTest->exportToXML( [['ID','firstName','lastName']],'/api/authors/export/XML',"authors");

    }

    /**
     * @test  exporting authors and books to XMl.
     */
    public function exportAuthorsAndBooksToXML(){
        $this->utilityTest->exportToXML( [['ID','firstName','lastName'], ['ID', 'title']],'/api/authors/export/XML/with-books',
            "authors","books");
    }



}

