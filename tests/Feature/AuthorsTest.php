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
 * a class to test /api/authors/... endpoints.
 */
class AuthorsTest extends TestCase
{

    //cleans up the DB before and after testing:
    use RefreshDatabase;
    // test without the need to send CSRF tokens to simplify http post, put and delete requests.
    use WithoutMiddleware;

    private $utilityTest,$titles,$authors,$invalidAuthors;

    /**
     * AuthorsTest constructor.
     * initiates helper class instance and populates some sample data to be used in the test.
     */
    public function __construct()
    {
        parent::__construct();
        $this->utilityTest = new UtilityTest();
        $this->authors = [
            [Authors::FIRSTNAME_FIELD => 'Ted', Authors::LASTNAME_FIELD  => 'Bake'],
            [Authors::FIRSTNAME_FIELD => "Updated", Authors::LASTNAME_FIELD  => "Hi"],
            [Authors::FIRSTNAME_FIELD => 'Updated', Authors::LASTNAME_FIELD  =>'Fail']
        ];
        $this->invalidAuthors = [
            [Authors::FIRSTNAME_FIELD => 1, Authors::LASTNAME_FIELD  => 2]
        ];
        $this->titles = ["Change Author Name"];

    }
    ##################################### Change an Author's Name ######################################################
    /**  @test changing an author's name in database.
     * 1. change name with invalid (i.e. non-existing) ID.
     * 2. change name with valid request.
     * 3. change name with no request body.
     * 4. change name with invalid name.
     */
    public function changeAuthorName()
    {
        //try to update with valid request but DB is empty (in other words, updating with invalid ID):
        $this->changeAuthorNameWithInvalidID();
        //create a book with an author:
        $createResponse = $this->utilityTest->createABook($this->titles[0], [$this->authors[0]] );
        //then, update an author through its ID:
         $this->changeAuthorNameWithValidRequest($createResponse[Authors::ID_FIELD][0],$this->authors[0]);
        //update with empty request body:
        $this->changeAuthorNameWithEmptyRequest();
        //update with invalid firstName and lastName:
        $this->changeAuthorNameWithInvalidName($createResponse[Authors::ID_FIELD][0]);
    }

    /** Changes an author's name with valid ID, firstName, and lastName.
     * @param integer $id, ID of an author that exists in the database.
     * @param array  $current, the author's firstName and lastName before changing.
     */
    public function changeAuthorNameWithValidRequest($id, $current)
    {
        $updateResponse = $this->json('PUT','/api/authors',
            [ Authors::ID_FIELD =>$id,
            Authors::FIRSTNAME_FIELD  => $this->authors[1][Authors::FIRSTNAME_FIELD] ,
            Authors::LASTNAME_FIELD  => $this->authors[1][Authors::LASTNAME_FIELD]
        ]);
        //checks that a proper response is returned:
        $this->utilityTest->checkOKResponseWithCustomMessage(
            $updateResponse,
            AuthorsController::CHANGE_NAME_SUCCEED_MESSAGE);

        //check that changes are successfully made in the database:
        $this->assertDatabaseHas(Authors::TABLE_NAME, $this->authors[1]);
        $this->assertDatabaseMissing(Authors::TABLE_NAME, $current);
    }

    /** requests to change an author's name with no request body. */
    public function changeAuthorNameWithEmptyRequest()
    {
        $updateResponse =  $this->json('PUT','/api/authors', []);
        $this->utilityTest->checkInvalidResponse($updateResponse);
    }

    /** Changes an author's name with invalid firstName and lastName.
     * @param integer $id, ID of an author that exists in the database.
     */
    public function changeAuthorNameWithInvalidName($id)
    {
        $updateResponse = $this->json('PUT','/api/authors',[
            Authors::ID_FIELD =>$id,
            Authors::FIRSTNAME_FIELD => $this->invalidAuthors[0][Authors::FIRSTNAME_FIELD],
            Authors::LASTNAME_FIELD => $this->invalidAuthors[0][Authors::LASTNAME_FIELD]]);
        $this->utilityTest->checkInvalidResponse($updateResponse);
        $this->assertDatabaseMissing(Authors::TABLE_NAME, $this->invalidAuthors[0]);
    }
    /** Changes an author's name with invalid ID. */
    public function changeAuthorNameWithInvalidID()
    {
        $updateResponse = $this->json('PUT','/api/authors',[
            Authors::ID_FIELD =>2,
            Authors::FIRSTNAME_FIELD =>  $this->authors[2][Authors::FIRSTNAME_FIELD],
            Authors::LASTNAME_FIELD => $this->authors[2][Authors::LASTNAME_FIELD]]);
        //check for failed update response:
        $this->utilityTest->checkOKResponseWithCustomMessage(
            $updateResponse,
            AuthorsController::CHANGE_NAME_FAILED_MESSAGE);
        //make sure changes aren't made in the database:
        $this->assertDatabaseMissing(Authors::TABLE_NAME, $this->authors[2]);
    }

    ################################## Searching For a Book By Author ##################################################
    /**  @test  search for a book by its author. */
    public function searchByAuthor()
    {
        $this->utilityTest->searchTestFacade('/api/authors/with-filter');
    }
    ################################## Export to CSV and XML ###########################################################
    /** @test requests for authors (only) content  exported as CSV and then check its content.   */
    public function exportAuthorsToCSV()
    {
        $this->utilityTest->exportToCSV( [Authors::ID_FIELD, Authors::FIRSTNAME_FIELD, Authors::LASTNAME_FIELD] ,
            '/api/authors/export/CSV', AuthorsController::AUTHORS_EXPORT_CSV_FILENAME);
    }
    /**  @test requests for books and authors content  exported as CSV and then check its content. */
    public function exportAuthorsAndBooksToCSV()
    {
        $this->utilityTest->exportToCSV( [Authors::ID_FIELD, Authors::FIRSTNAME_FIELD, Authors::LASTNAME_FIELD,
            Books::ID_FIELD, Books::TITLE_FIELD] , '/api/authors/export/CSV/with-books',
            PivotController::AUTHORS_AND_BOOKS_EXPORT_CSV_FILENAME);
    }

    /**  @test  requests for a authors (only) content  exported as XML and then check its content. */
    public function exportAuthorsToXML()
    {
        $this->utilityTest->exportToXML( [[Authors::ID_FIELD, Authors::FIRSTNAME_FIELD, Authors::LASTNAME_FIELD]],
            '/api/authors/export/XML', Authors::TABLE_NAME);
    }

    /**  @test requests for authors and books content exported as XML and then check its content. */
    public function exportAuthorsAndBooksToXML()
    {
        $this->utilityTest->exportToXML( [[Authors::ID_FIELD, Authors::FIRSTNAME_FIELD, Authors::LASTNAME_FIELD],
            [Books::ID_FIELD, Books::TITLE_FIELD]], '/api/authors/export/XML/with-books',
            Authors::TABLE_NAME, Books::TABLE_NAME);
    }
}

