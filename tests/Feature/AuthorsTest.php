<?php


namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;
use App\Http\Controllers\AuthorsController;
use App\Authors;
use App\Http\Controllers\PivotController;
use App\Books;

/**
 * Class AuthorsTest
 * @package Tests\Feature
 * a class to test /api/authors/... endpoint
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
         $this->changeAuthorNameWithValidRequest($createResponse[Authors::ID_FIELD][0],$this->authors[0]);
        //update with empty body:
        $this->changeAuthorNameWithEmptyRequest();
        //update with invalid firstName and lastName:
        $this->changeAuthorNameWithInvalidName($createResponse[Authors::ID_FIELD][0]);
    }

    /** Changes an author's name with valid ID, firstName, and lastName.
     * @param $id, ID of an author that exists in the database.
     * @param $current, the author's current firstName and lastName.
     */
    public function changeAuthorNameWithValidRequest($id, $current)
    {
        $updateResponse = $this->json('PUT','/api/authors',
            [ Authors::ID_FIELD =>$id,
            Authors::FIRSTNAME_FIELD  => 'Updated',
            Authors::LASTNAME_FIELD  =>'Updated'
        ]);
        //checks that a proper response is returned:
        $this->utilityTest->checkOKResponseWithCustomMessage(
            $updateResponse,
            AuthorsController::CHANGE_NAME_SUCCEED_MESSAGE);

        //check that changes are successfully made in the database:
        $this->assertDatabaseHas(Authors::TABLE_NAME, $this->authors[2]);
        $this->assertDatabaseMissing(Authors::TABLE_NAME, $current);
    }

    /** requests to change an author's name with no request body. */
    public function changeAuthorNameWithEmptyRequest()
    {
        $updateResponse =  $this->json('PUT','/api/authors', []);
        $this->utilityTest->checkInvalidResponse($updateResponse);
    }

    /** Changes an author's name with invalid firstName and lastName.
     * @param $id, ID of an author that exists in the database.
     */
    public function changeAuthorNameWithInvalidName($id)
    {
        $invalidName = ['firstName' => 123, 'lastName' => 456];
        $updateResponse = $this->json('PUT','/api/authors',[
            Authors::ID_FIELD =>$id,
            Authors::FIRSTNAME_FIELD => 123,
            Authors::LASTNAME_FIELD => 456]);
        $this->utilityTest->checkInvalidResponse($updateResponse);
        $this->assertDatabaseMissing(Authors::TABLE_NAME, $invalidName);
    }
    /** Changes an author's name with invalid ID.
     * @param $id, ID of an author that does not exist in the database.
     */
    public function changeAuthorNameWithInvalidID()
    {
        $updateResponse = $this->json('PUT','/api/authors',[
            Authors::ID_FIELD =>2,
            Authors::FIRSTNAME_FIELD =>  "Updated",
            Authors::LASTNAME_FIELD => "Wrong"]);
        $this->utilityTest->checkOKResponseWithCustomMessage(
            $updateResponse,
            AuthorsController::CHANGE_NAME_FAILED_MESSAGE);
        $this->assertDatabaseMissing(Authors::TABLE_NAME, $this->authors[1]);
    }

    /**  @test  search for a book by its author. */
    public function searchByAuthor()
    {
        $this->utilityTest->searchTestFacade('/api/authors/with-filter');
    }
    /** @test requests for a authors (only) content  exported as a CSV and then check its content.   */
    public function exportAuthorsToCSV()
    {
        $this->utilityTest->exportToCSV( [Authors::ID_FIELD, Authors::FIRSTNAME_FIELD, Authors::LASTNAME_FIELD,] ,
            '/api/authors/export/CSV', AuthorsController::AUTHORS_EXPORT_CSV_FILENAME);
    }
    /**  @test requests for a books and authors content  exported as a CSV and then check its content. */
    public function exportAuthorsAndBooksToCSV()
    {
        $this->utilityTest->exportToCSV( [Authors::ID_FIELD, Authors::FIRSTNAME_FIELD, Authors::LASTNAME_FIELD,
            Books::ID_FIELD, Books::TITLE_FIELD] , '/api/authors/export/CSV/with-books',
            PivotController::AUTHORS_AND_BOOKS_EXPORT_CSV_FILENAME);
    }
    /**  @test  requests for a authors (only) content  exported as XML and then check its content. */
    public function exportAuthorsToXML(){
        $this->utilityTest->exportToXML( [[Authors::ID_FIELD, Authors::FIRSTNAME_FIELD, Authors::LASTNAME_FIELD]],
            '/api/authors/export/XML', Authors::TABLE_NAME);

    }

    /**  @test requests for authors and books  content  exported as XML and then check its content. */
    public function exportAuthorsAndBooksToXML(){
        $this->utilityTest->exportToXML( [[Authors::ID_FIELD, Authors::FIRSTNAME_FIELD, Authors::LASTNAME_FIELD],
            [Books::ID_FIELD, Books::TITLE_FIELD]], '/api/authors/export/XML/with-books',
            Authors::TABLE_NAME, Books::TABLE_NAME);
    }
}

