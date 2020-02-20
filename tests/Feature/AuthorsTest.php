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

    /**
     * @test updating an author's name in database.
     */
    public function changeAuthorName()
    {
        $newAuthor = ['firstName' => 'Midoriya', 'lastName' => 'Zoldyck'];

        //firstly, must create a book with an author:
        $createResponse = $this->json('POST','/api/books',['title'=>'To Be Updated',
            'newAuthors' => [
                $newAuthor
            ]
        ]);

        $createResponse->assertStatus(201);
        $this->assertTrue(gettype($createResponse['newAuthorsID'][0]) == "integer");
        $this->assertDatabaseHas('authors', $newAuthor);
        //BUG: try update with an invalid ID :
//        $updateResponse = $this->json('PUT','/api/authors',['ID'=>strval($createResponse['newAuthorsID'][0]),
//            'firstName' => 'Updated',
//            'lastName' =>'Updated'
//        ]);
//        $updateResponse->assertStatus(400);
        //then, update an author it through its ID:
        $updateResponse = $this->json('PUT','/api/authors',['ID'=>$createResponse['newAuthorsID'][0],
            'firstName' => 'Updated',
            'lastName' =>'Updated'
        ]);
        $this->assertDatabaseHas('authors', ['firstName' => 'Updated',
            'lastName' =>'Updated']);
        $this->assertDatabaseMissing('authors', $newAuthor);
        //update with empty body:
        $updateResponse = $this->json('PUT','/api/authors',[]);
        $updateResponse->assertStatus(400);
        //update with ID only:
        $updateResponse = $this->json('PUT','/api/authors',['ID'=>$createResponse['newAuthorsID'][0]  ]);
        $updateResponse->assertStatus(400);
        //update with invalid firstName and lastName:
        $updateResponse = $this->json('PUT','/api/authors',['ID'=>$createResponse['newAuthorsID'][0],
        'firstName' => 123, 'lastName' => 456]);
        $updateResponse->assertStatus(400);
        //update with invalid firstName:
        $updateResponse = $this->json('PUT','/api/authors',['ID'=>$createResponse['newAuthorsID'][0],
            'firstName' => 123, 'lastName' => "John"]);
        $updateResponse->assertStatus(400);
        //update with invalid lastName:
        $updateResponse = $this->json('PUT','/api/authors',['ID'=>$createResponse['newAuthorsID'][0],
            'firstName' => "John", 'lastName' => 456]);
        $updateResponse->assertStatus(400);
        //update with firstName and lastName only:
        $updateResponse = $this->json('PUT','/api/authors',[$newAuthor]);
        $updateResponse->assertStatus(400);
    }
    /**
     * @test  search for a book by its author.
     */
    public function searchByAuthor()
    {
        $newAuthor = ['firstName' => 'Midoriya', 'lastName' => 'Zoldyck'];
        $title = 'Search';
        //firstly, must create a book with an author:
        $createResponse = $this->json('POST','/api/books',['title'=>$title,
            'newAuthors' => [
                $newAuthor
            ]
        ]);
        //then, search by the author's name:
        $searchResponse = $this->json('GET','/api/authors/with-filter',$newAuthor);

        $src = ["authorID"=> $createResponse['newAuthorsID'][0],
            "firstName"=> $newAuthor['firstName'],
            "lastName"=> $newAuthor['lastName'],
            "bookID"=> $createResponse["bookID"] ,
            "title"=> $title];

        $this->utilityTest->checkJsonContent($searchResponse,$src);

        //make sure search for exact matches only
        $searchResponse = $this->json('GET','/api/authors/with-filter', ['firstName' =>$newAuthor['firstName'][0] ,
            'lastName' => $newAuthor['lastName']]);
        //expect for empty json response:
        $this->utilityTest->checkEmptyJsonContent($searchResponse);

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
        $this->utilityTest->exportToXML( '/api/authors/export/XML',"authors");


    }


}

