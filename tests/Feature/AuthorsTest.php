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
    


}
