<?php


namespace Tests\Feature;
use Tests\TestCase;

//a class to test most of the /api/authors endpoint
class AuthorsTest extends TestCase
{
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
        //then, update an author it through its ID:
        $updateResponse = $this->json('PUT','/api/authors',['ID'=>$createResponse['newAuthorsID'][0],
            'firstName' => 'Updated',
            'lastName' =>'Updated'
        ]);
        $this->assertDatabaseHas('authors', ['firstName' => 'Updated',
            'lastName' =>'Updated']);
        $this->assertDatabaseMissing('authors', $newAuthor);


    }

}
