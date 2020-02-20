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
        //firstly, test that it is empty:
        $response = $this->get('api/books');
        $response
            ->assertStatus(200)
            ->assertExactJson( []);

        //then, create a book with an author:
        $newAuthor = ['firstName' => 'Midoriya', 'lastName' => 'Zoldyck'];
        $title = 'Alpha Beta';
         $createResponse = $this->json('POST','/api/books',['title'=>$title,
            'newAuthors' => [
                $newAuthor
            ]
        ]);

        $createResponse->assertStatus(201);
        //make sure response has the author's ID and book's ID:
        $this->assertTrue(gettype($createResponse['bookID']) == "integer");
        $this->assertTrue(gettype($createResponse['newAuthorsID'][0]) == "integer");
        //make sure database has both author and book:
        $this->assertDatabaseHas('books', ['title'=>'Alpha Beta']);
        $this->assertDatabaseHas('authors', $newAuthor);

        //now test that they are returned when doing a get request, and nothing else is returned besides that:
        $response = $this->get('api/books');
        $response
            ->assertStatus(200)
            ->assertExactJson( [[
                "ID"=> $createResponse['newAuthorsID'][0],
                "firstName"=> $newAuthor['firstName'],
                "lastName"=> $newAuthor['lastName'],
                "books_ID"=> $createResponse["bookID"] ,
                "title"=> $title
            ] ]);

    }
    /**
     * @test search for a book by its title.
     */
    public function searchByTitle(){
        $newAuthor = ['firstName' => 'Midoriya', 'lastName' => 'Zoldyck'];
        $title = 'Search';
        //firstly, must create a book with an author:
        $createResponse = $this->json('POST','/api/books',['title'=>$title,
            'newAuthors' => [
                $newAuthor
            ]
        ]);
        $createResponse->assertStatus(201);
        $this->assertDatabaseHas('books',['title'=>$title] );
        $searchResponse = $this->json('GET','/api/books/with-filter',['title'=>$title]);
        $searchResponse
            ->assertStatus(200)
            ->assertExactJson( [[
                "ID"=> $createResponse['newAuthorsID'][0],
                "firstName"=> $newAuthor['firstName'],
                "lastName"=> $newAuthor['lastName'],
                "books_ID"=> $createResponse["bookID"] ,
                "title"=> $title
            ] ]);
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

        $response->assertStatus(201);
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
        //BUG: may break if someone has an ID of 1.
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





}
