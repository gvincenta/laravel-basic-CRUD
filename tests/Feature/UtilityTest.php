<?php


namespace Tests\Feature;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;
use App\Exports\DBExport;


class UtilityTest extends TestCase
{
    public function createABook($title, $newAuthor, $existingAuthors ){
        return   $this->json('POST','/api/books',['title'=>$title,
            'newAuthors' => $newAuthor,
            'authors' => $existingAuthors
        ]);
    }
    public function checkJsonContent($searchResponse, $src){
        $searchResponse
            ->assertStatus(200)
            ->assertExactJson( [[
                "ID"=> $src['authorID'],
                "firstName"=> $src['firstName'],
                "lastName"=> $src['lastName'],
                "books_ID"=> $src["bookID"] ,
                "title"=> $src['title']
            ] ]);

    }
    public function checkEmptyJsonContent($searchResponse){
        $searchResponse
            ->assertStatus(200)
            ->assertExactJson( []);
    }
    public function __construct()
    {
        parent::__construct();
        parent::setUp();
    }
    //refactoring exportAuthorToCSV still failed.
    public function exportAuthorToCSV($newAuthor, $title , $headersToBeChecked,$url){

        //firstly, must create a book with an author:
        $createResponse = $this->createABook($title, [$newAuthor],[]);

        $src = ["ID"=> $createResponse['newAuthorsID'][0],
            "firstName"=> $newAuthor['firstName'],
            "lastName"=> $newAuthor['lastName'],
            "books_ID"=> $createResponse["bookID"] ,
            "title"=> $title];
        Excel::fake();

        $this->get($url);

        Excel::assertDownloaded('authors.csv', function(DBExport $export) use($src,$headersToBeChecked) {
            // Assert that the correct export is downloaded.

            foreach( $headersToBeChecked as $header) {
                if (! $export->collection()->contains($header,$src->$header)){

                    return false;
                }
            }

            return true;
        });



    }
}
