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
    //search for a book a valid title / firstName and lastName:
    public function searchWithValidRequest($url,$src){
        $searchResponse = $this->json('GET',$url,[
            'title'=>$src["title"], //for a search by title
            'firstName' =>$src['firstName'] , //for a search by author
            'lastName' => $src['lastName']
        ]);

        //check the response:
        $this->checkJsonContent($searchResponse,$src);
    }
    //make sure only return exact matches by sending non-exact requests.
    public function searchExactMatchOnly($url,$src){
        $searchResponse = $this->json('GET',$url,['title'=>$src["title"][0],
            'firstName' =>$src['firstName'][0],
            'lastName' => $src['lastName']
        ]);
        //expect for an empty response:
        $this->checkEmptyJsonContent($searchResponse);
    }

    /** Conducts searching test by an author or a book's title:
     * 1. tests for with valid request
     * 2. tests with non-exact request
     * 3. tests with invalid request
     * @param $url the url to send the search request to.
     */
    public function searchTestFacade($url){
        $newAuthor = ['firstName' => 'Midoriya', 'lastName' => 'Zoldyck'];
        $title = 'Search';
        $createResponse =  $this->createABook($title, [$newAuthor], [] );
        //combine all resources into 1 variable:
        $src = ["authorID"=> $createResponse['newAuthorsID'][0],
            "firstName"=> $newAuthor['firstName'],
            "lastName"=> $newAuthor['lastName'],
            "bookID"=> $createResponse["bookID"] ,
            "title"=> $title ] ;

        //then search on the recently created book:
        $this->searchWithValidRequest($url,$src);

        //make sure  search for exact matches only:
        $this->searchExactMatchOnly($url,$src);

        //try to send invalid requests:
        $searchResponse =  $this->sendEmptyRequest($url,'GET');
        //expect for status: 400 response:
        $this->checkInvalidRequestResponse($searchResponse);
    }
    public function sendEmptyRequest($url,$verb){
        return $this->json($verb,$url, []);
    }
    public function exportToXML($headersToBeChecked, $url, $rootTag ,$nestedTag=""){
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

        $src = [ "authorID"=> $createResponse['newAuthorsID'][0],
            "firstName"=> $newAuthor["firstName"],
            "lastName"=> $newAuthor["lastName"],
            "title"=> $title,
            "bookID"=> $createResponse["bookID"]];



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
        $childArray = $object->data;

       $this->checkNestedXML($headersToBeChecked,$childArray,$src,$nestedTag,$object->data,$rootTag);

    }
    //check if the xml has a nested element, e.g. books under authors or vice versa:
    public function checkNestedXML($headersToBeChecked,$childArray,$src,$nestedTag, $xml,$rootTag){
        //if no nesting, just loop:
        $this->validateXMLContent($headersToBeChecked[0],$childArray,$src,$rootTag);

        //if nesting, you must expand again, and loop:
        if ($xml->$nestedTag){
             //look for corresponding <data>:
            $this->assertObjectHasAttribute("data", $xml->$nestedTag);
            $grandChildArray = $xml->$nestedTag->data;
            //nesting only occurs once, so don't worry about recursion, just directly validate the $grandChildArray:

            $this->validateXMLContent($headersToBeChecked[1],$grandChildArray,$src,$nestedTag);

        }
    }
    //check that current xml elements under <data> are valid:
    public function validateXMLContent($headersToBeChecked,$childArray,$src,$currentTag){
        foreach ($headersToBeChecked as $header) {
            if ($currentTag == "authors" && $header=="ID"){
                $this->assertTrue($src["authorID"] == $childArray->$header);
            } else if ($currentTag == "books" && $header=="ID"){
                $this->assertTrue($src["bookID"] == $childArray->$header);
            }else{
                $this->assertTrue($src[$header] == $childArray->$header);
            }
        }
    }
    public function checkInvalidRequestResponse($response){
        $response
            ->assertStatus(400)
            ->assertExactJson(["message" => "invalid request"]);
    }

}
