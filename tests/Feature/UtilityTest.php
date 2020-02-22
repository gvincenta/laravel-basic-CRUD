<?php


namespace Tests\Feature;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;
use App\Exports\DBExport;
use App\Books;
use App\Authors;
use App\Http\Controllers\PivotController;

/**
 * Class UtilityTest
 * @package Tests\Feature\
 * used to make helper functions to be used by other feature testing classes.
 * TODO: be more specific on the XML testing method?
 */
class UtilityTest extends TestCase
{
    use WithoutMiddleware;

    public function __construct()
    {
        parent::__construct();
        parent::setUp();
    }
    /** Used to post to /api/books route to make a new book and assign authors to it.
     * @param $title required to specify the new book's title.
     * @param array $newAuthor to assign non-existing authors to this new book. (optional)
     * @param array $existingAuthors to assign existing authors (in database) to this new book. (optional)
     * @return mixed the response from the route.
     */
    public function createABook($title, $newAuthor = [], $existingAuthors = [] ){
        return   $this->json('POST','/api/books',[
            Books::TITLE_FIELD=>$title,
            PivotController::NEW_AUTHORS_REQUEST => $newAuthor,
            PivotController::EXISTING_AUTHORS_REQUEST => $existingAuthors
        ]);
    }

    /** checks whether a response returns a certain json structure.
     * @param $sresponse  the response to be checked against.
     * @param $src the expected value that the response should contain.
     */
    public function checkJsonContent($sresponse, $src){
        $sresponse
            ->assertStatus(200)
            ->assertExactJson( [[
                    Authors::ID_FIELD=> $src[Authors::ID_FIELD] ,
                    Authors::FIRSTNAME_FIELD=> $src[Authors::FIRSTNAME_FIELD],
                    Authors::LASTNAME_FIELD=> $src[Authors::LASTNAME_FIELD],
                    Books::TITLE_FIELD=> $src[Books::TITLE_FIELD],
                    Books::ID_FIELD=> $src[Books::ID_FIELD]
            ] ]);

    }

    /** checks for empty json response.
     * @param $response the response from backend to be checked.
     */
    public function checkEmptyJsonContent($response){
        $response
            ->assertStatus(200)
            ->assertExactJson( []);
    }


    /** Tests whether an exported CSV file is empty or not.
     * @param $url specifies where to get the CSV file from.
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
     * @param $headersToBeChecked the headers to be checked against the CSV file
     * @param $url specifies where to get the CSV file.
     * @param $fileName the name of the CSV file that is (assumed to be) downloaded.
     * */
    public function exportToCSV($headersToBeChecked, $url, $fileName){
        $this->exportEmptyCSV($url,$fileName);

        //firstly,  create a book with an author:
        $newAuthor = ['firstName' => 'Midoriya', 'lastName' => 'Zoldyck'];
        $title = 'Search';
        $createResponse = $this->createABook($title, [$newAuthor],[]);

        //construct the values to be checked against:
        $src = [Authors::ID_FIELD=> $createResponse[Authors::ID_FIELD][0],
            Authors::FIRSTNAME_FIELD=> $newAuthor[Authors::FIRSTNAME_FIELD],
            Authors::LASTNAME_FIELD=> $newAuthor[Authors::LASTNAME_FIELD],
            Books::TITLE_FIELD=> $title,
            Books::ID_FIELD=> $createResponse[Books::ID_FIELD]];




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
    /** testing the search functionality with a valid title / author.
     * @param $url specifies where to send the search request to.
     * @param $src contains the search keyword (title, firstName and lastName).
     */
    public function searchWithValidRequest($url,$src){
        $searchResponse = $this->json('GET',$url,[
            Books::TITLE_FIELD=>$src[Books::TITLE_FIELD], //for a search by title
            Authors::FIRSTNAME_FIELD =>$src[Authors::FIRSTNAME_FIELD] , //for a search by author
            Authors::LASTNAME_FIELD => $src[Authors::LASTNAME_FIELD] //for a search by author
        ]);

        //check the response:
        $this->checkJsonContent($searchResponse,$src);
    }
    //

    /** make sure search for a book only works with  exact matches. this is tested by sending non-exact match requests.
     * @param $url the route to send the request to.
     * @param $src the source that has the exact matching values for the book's title or author.
     */
    public function searchExactMatchOnly($url,$src){
        $searchResponse = $this->json('GET',$url,
            [Books::TITLE_FIELD => $src[Books::TITLE_FIELD][0],
            Authors::FIRSTNAME_FIELD =>$src[Authors::FIRSTNAME_FIELD][0],
            Authors::LASTNAME_FIELD => $src[Authors::LASTNAME_FIELD]
        ]);
        //expect for an empty response:
        $this->checkEmptyJsonContent($searchResponse);
    }

    /** Conducts tests for searching  by an author or a book's title:
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
        $src = [Authors::ID_FIELD=> $createResponse[Authors::ID_FIELD][0],
            Authors::FIRSTNAME_FIELD=> $newAuthor[Authors::FIRSTNAME_FIELD],
            Authors::LASTNAME_FIELD=> $newAuthor[Authors::LASTNAME_FIELD],
            Books::TITLE_FIELD=> $title,
            Books::ID_FIELD=> $createResponse[Books::ID_FIELD]];


        //then search on the recently created book:
        $this->searchWithValidRequest($url,$src);

        //make sure  search for exact matches only:
        $this->searchExactMatchOnly($url,$src);

        //try to send invalid requests:
        $searchResponse =  $this->json('GET',$url, []);
        //expect for status: 400 response:
        $this->checkInvalidResponse($searchResponse);
    }

    /** Sends a request with no json body to a specific route.
     * @param $url, the url to send the request to.
     * @param $verb, the HTML Verb (POST, PUT, DELETE, GET).
     * @return mixed, the response from backend.
     */
    public function sendEmptyRequest($url,$verb){
        return $this->json($verb,$url, []);
    }

    /**
     * @param $headersToBeChecked
     * @param $url
     * @param $rootTag
     * @param string $nestedTag
     */
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

        $src = [ Authors::ID_FIELD=> $createResponse[Authors::ID_FIELD][0],
            Authors::FIRSTNAME_FIELD=> $newAuthor[Authors::FIRSTNAME_FIELD],
            Authors::LASTNAME_FIELD=> $newAuthor[Authors::LASTNAME_FIELD],
            Books::TITLE_FIELD=> $title,
            Books::ID_FIELD=> $createResponse[Books::ID_FIELD]];
        /*  TODO: add more examples!
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

       $this->checkNestedXML($headersToBeChecked,$childArray,$src,$nestedTag,$object->data);

    }
    //

    /** check if the xml has a nested element, e.g. books under authors or vice versa:
     * @param $headersToBeChecked, contains the headers to be checked against the XML file.
     * @param $childArray, the
     * @param $src, contains the expected values of each header to be checked against the XML file.
     * @param $nestedTag, the tag that indicates the XML has a nested element that needs to be checked again.
     * @param $xml, the xml file to be checked against.
     * @param $rootTag, authors OR books.
     */
    public function checkNestedXML($headersToBeChecked,$childArray,$src,$nestedTag, $xml){
        //if no nesting, just loop:
        $this->validateXMLContent($headersToBeChecked[0],$childArray,$src );

        //if nesting, you must expand again, and loop:
        if ($xml->$nestedTag){
             //look for corresponding <data>:
            $this->assertObjectHasAttribute("data", $xml->$nestedTag);
            $grandChildArray = $xml->$nestedTag->data;
            //nesting only occurs once, so don't worry about recursion, just directly validate the $grandChildArray:

            $this->validateXMLContent($headersToBeChecked[1],$grandChildArray,$src );

        }
    }

    /** check that current xml elements under <data> are valid:
     * @param $headersToBeChecked the headers to be checked against in the XML file.
     * @param $childArray
     * @param $src , contains the expected values of each header to be checked against the XML file.
     * @param $currentTag authors OR books.
     */
    public function validateXMLContent($headersToBeChecked,$childArray,$src){
        foreach ($headersToBeChecked as $header) {
                $this->assertTrue($src[$header] == $childArray->$header);
        }
    }
    /** When you send an invalid request, the backend returns an "invalid request" message. check for this kind of
     * response here.
     * @param $response the response from backend to be checked against.
     */
    public function checkInvalidResponse($response){
        $response
            ->assertStatus(400)
            ->assertExactJson(["message" => "invalid request"]);
    }

    /** When you send a valid requests, backend may repsonse with OK status with a customised message. check for this
     * kind of message here.
     * @param $response the response from backend to be checked against.
     * @param $message the customised message expected from backend (usually a success / failure message).
     */
    public function checkOKResponseWithCustomMessage($response, $message){
        $response
            ->assertStatus(200)
            ->assertExactJson(["message" => $message]);
    }
}
