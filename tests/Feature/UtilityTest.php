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
    /** Tests whether an exported CSV file is empty or not.
     *
     * @param $url specifies where to get the CSV file.
     * */
    public function exportEmptyCSV($url,$fileName){
        Excel::fake();
        //get the data:
        $response = $this->get($url);
        //assert OK status:
        $response->assertStatus(200);
        //check CSV content and check if it's downloaded or not:
        Excel::assertDownloaded( $fileName, function(DBExport $export)  {
            // expect collection to be empty:
            return $export->collection()->isEmpty();
        });
    }
    /** Tests an exported CSV file according to the headers that need to be checked against.
     *
     * @param $headersToBeChecked the headers to be checked against the CSV file
     * @param $url specifies where to get the CSV file.
     * @param $fileName the name of the CSV file that is downloaded.
     * */
    public function exportToCSV($headersToBeChecked, $url, $fileName){
        $this->exportEmptyCSV($url,$fileName);

        //firstly,  create a book with an author:
        $newAuthor = ['firstName' => 'Midoriya', 'lastName' => 'Zoldyck'];
        $title = 'Search';
        $createResponse = $this->createABook($title, [$newAuthor],[]);

        //construct the values to be checked against:
        $src = ["ID"=> $createResponse['newAuthorsID'][0],
            "firstName"=> $newAuthor['firstName'],
            "lastName"=> $newAuthor['lastName'],
            "books_ID"=> $createResponse["bookID"] ,
            "title"=> $title];
        //for books, the ID is the bookID:
        if($url == '/api/books/export/CSV'){
            $src["ID"] = $createResponse["bookID"] ;
        }


         Excel::fake();
         //get the data:
        $response = $this->get($url);
        //assert OK status:
        $response->assertStatus(200);
        //check CSV content and check if it's downloaded or not:
        Excel::assertDownloaded($fileName, function(DBExport $export) use($src,$headersToBeChecked) {
            // loop through each header and check the values recorded in CSV file:
            foreach($headersToBeChecked as $header){
                if (! $export->collection()->contains($header, $src[$header] )){
                    return false;
                }

            }

            return true;
        });



    }
    public function exportToXML( $url, $rootTag ){
        //firstly, must create a book with an author:
        $newAuthor = ['firstName' => 'Midoriya', 'lastName' => 'Zoldyck'];
        $title = 'Search';
        $createResponse =  $this->createABook($title, [$newAuthor], [] );
        //now, get the xml output:
        $response = $this->get($url);
        //load it as an object:
        $object = simplexml_load_string($response->getContent());
        //check for a proper object type:
        $this->assertNotFalse($object);
        $this->assertInstanceOf( \SimpleXMLElement::class, $object);

        $src = [ "ID"=> $createResponse['newAuthorsID'][0],
            "firstName"=> $newAuthor["firstName"],
            "lastName"=> $newAuthor["lastName"],
            "title"=> $title];
        if($url == '/api/books/export/XML'){
            dd("rumnnnn");
            $src["ID"] = $createResponse["bookID"] ;
        }
        /*
         * structure to be validated:
         * <?xml version="1.0"?>
            <authors>
              <data>
                <ID>102</ID>
                <firstName>Hello</firstName>
                <lastName>World</lastName>
              </data>
            </authors> */

        //check the root tag <authors>:
        $this->assertTrue($object->getName() == $rootTag);
        //look for corresponding <data>:
        $this->assertObjectHasAttribute("data", $object);
        // check the values for child tags of <data>:
        $childArray = $object->data->attributes();

        foreach ($object->data->attributes() as $key=> $val ) {
            $this->assertTrue($src[$key] == $childArray[$key]);
        }

    }
}
