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

    /**
     * @test tests a get request for Books And Authors table.
     */
    public function getBooksAndAuthors()
    {
        $response = $this->get('api/books');

        $response->assertStatus(200);

    }
    /**
     * @test adding a book and assigns authors to it.
     */
    public function addABookWithAuthor()
    {
        //testing for valid inputs:


        $newAuthor = ['firstName' => 'Midoriya', 'lastName' => 'Zoldyck'];
        $newAuthor2 = ['firstName' => 'Michael', 'lastName' => 'AJ'];
        $response = $this->json('POST','/api/books',['title'=>'Alpha Beta',
            'newAuthors' => [
                $newAuthor,$newAuthor2
            ]
        ]);
        //TODO : assert status change to 201
        $response->assertStatus(200);
        $this->assertDatabaseHas('books', ['title'=>'Alpha Beta']);
        $this->assertDatabaseHas('authors', $newAuthor);
        $this->assertDatabaseHas('authors', $newAuthor2);
        //todo: make sure books connected to newly created authors:

        //invalid input #1: non-string titles
        $response = $this->json('POST','/api/books',['title'=>123,
            'newAuthors' => [
                $newAuthor
            ]
        ]);
        $response->assertStatus(400);
        $this->assertDatabaseMissing ('books', ['title'=>123]);
        //invalid input #2: empty titles
        $response = $this->json('POST','/api/books',['title'=> "",
            'newAuthors' => [
                $newAuthor
            ]
        ]);
        $response->assertStatus(400);
        $this->assertDatabaseMissing('books', ['title'=>'']);
        //invalid input #3: no authors
        $response = $this->json('POST','/api/books',['title'=> "No Authors"]);
        $response->assertStatus(400);
        $this->assertDatabaseMissing('books', ['title'=>'No Authors']);
        //invalid input #4: new authors exist with no body
        $response = $this->json('POST','/api/books',['title'=> "new authors exist with no body",
            "newAuthors" =>  []]);
        $response->assertStatus(400);
        $this->assertDatabaseMissing('books', ['title'=>'new authors exist with no body']);
        //invalid input #5: existing authors exist with no body
        $response = $this->json('POST','/api/books',['title'=> "existing authors exist with no body",
            "authors" =>  []]);
        $response->assertStatus(400);
        $this->assertDatabaseMissing('books', ['title'=>'existing authors exist with no body']);
        //invalid input #6: new authors exist with improper body
        $response = $this->json('POST','/api/books',['title'=> "new authors exist with improper body",
            "newAuthors" =>  ['firstName' =>123, 'lastName'=>456]]);
        $response->assertStatus(400);
        $this->assertDatabaseMissing('books', ['title'=>'new authors exist with improper body']);
        //invalid input #7: existing authors exist with improper body
        $response = $this->json('POST','/api/books',['title'=> "existing authors exist with improper body",
            "authors" =>  ['ID' => '1']]);
        $response->assertStatus(400);
        $this->assertDatabaseMissing('books', ['title'=>'existing authors exist with improper body']);
    }
    /**
     * @test deleting a book in the database.
     */
    public function deleteABook()
    {

        $newAuthor = ['firstName' => 'Midoriya', 'lastName' => 'Zoldyck'];
        //these should be missing due to using RefreshDatabase:
        $this->assertDatabaseMissing ('books', ['title'=>'To Be Deleted']);
        $this->assertDatabaseMissing('authors',$newAuthor);
        //firstly, must create a book:
        $createResponse = $this->json('POST','/api/books',['title'=>'To Be Deleted',
            'newAuthors' => [
                $newAuthor
            ]
        ]);
        $createResponse->assertStatus(200);
        $this->assertTrue(gettype($createResponse['bookID']) == "integer");
        $this->assertDatabaseHas('books', ['title'=>'To Be Deleted']);
        $this->assertDatabaseHas('authors', $newAuthor);


        //then, delete it through its ID:
        $deleteResponse = $this->json('DELETE','/api/books',['ID'=> $createResponse['bookID']]);
        //TODO : assert status (204 or 202 is appropriate here)
        $this->assertDatabaseMissing('books', ['title'=>'To Be Deleted','ID'=> $createResponse['bookID']]);
    }




}
