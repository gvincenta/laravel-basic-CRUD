<?php


namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;
use App\Exports\DBExport;
use App\Books;
use App\Authors;
use App\Http\Controllers\PivotController;
use App\Http\Controllers\UtilityController;
/**
 * Class UtilityTest
 * @package Tests\Feature\
 * contains helper functions to be used by other feature testing classes.
 */
class UtilityTest extends TestCase
{
    use WithoutMiddleware;
    private $authors,$titles;

    /**
     * UtilityTest constructor.
     * populates some sample data to be used in the test.
     */
    public function __construct()
    {
        parent::__construct();
        parent::setUp();

        $this->authors = [
            [Authors::FIRSTNAME_FIELD => 'Gil', Authors::LASTNAME_FIELD => 'Bert'],
            [Authors::FIRSTNAME_FIELD => 'Kumo', Authors::LASTNAME_FIELD => 'Maru'],
            [Authors::FIRSTNAME_FIELD => 'Moku', Authors::LASTNAME_FIELD => 'Sumo']
        ];
        $this->titles = ['Search','Hello','Goodbye'];
    }

    ####################################### Commonly Used Functions ####################################################
    /** Used to post to /api/books route to make a new book and assign authors to it.
     * @param string $title, required to specify the new book's title.
     * @param array $newAuthor, to assign non-existing authors to this new book.
     * @param array $existingAuthors, to assign existing authors to this new book.
     * @return mixed, the response from the route.
     */
    public function createABook($title, $newAuthor = [], $existingAuthors = [] ){
        return   $this->json('POST','/api/books',[
            Books::TITLE_FIELD=>$title,
            PivotController::NEW_AUTHORS_REQUEST => $newAuthor,
            PivotController::EXISTING_AUTHORS_REQUEST => $existingAuthors
        ]);
    }

    /** checks whether a response returns a certain json structure.
     * @param  \Illuminate\Http\Response $sresponse,  the response to be checked against.
     * @param array $src, the expected value that the response should contain.
     */
    public function checkJsonContent($sresponse, $src){
        $sresponse
            ->assertStatus(UtilityController::OK_STATUS)
            ->assertExactJson( [[
                    Authors::ID_FIELD=> $src[Authors::ID_FIELD],
                    Authors::FIRSTNAME_FIELD=> $src[Authors::FIRSTNAME_FIELD],
                    Authors::LASTNAME_FIELD=> $src[Authors::LASTNAME_FIELD],
                    Books::TITLE_FIELD=> $src[Books::TITLE_FIELD],
                    Books::ID_FIELD=> $src[Books::ID_FIELD]
            ] ]);
    }

    /** checks for empty json response.
     * @param \Illuminate\Http\Response $response, the response from backend to be checked.
     */
    public function checkEmptyJsonContent($response){
        $response
            ->assertStatus(UtilityController::OK_STATUS)
            ->assertExactJson([]);
    }

    /** When you send an invalid request, the backend returns "invalid request" message. check for this kind of
     * response here.
     * @param \Illuminate\Http\Response $response, the response from backend to be checked against.
     */
    public function checkInvalidResponse($response){
        $response
            ->assertStatus(UtilityController::INVALID_REQUEST_STATUS)
            ->assertExactJson([UtilityController::MESSAGE_RESPONSE_KEY => UtilityController::INVALID_REQUEST_MESSAGE]);
    }

    /** When you send a valid requests, backend may respond with OK status with a customised message. check for this
     * kind of response here.
     * @param \Illuminate\Http\Response response, the response from backend to be checked against.
     * @param string $message, the customised message expected from backend (usually a success / failure message).
     */
    public function checkOKResponseWithCustomMessage($response, $message){
        $response
            ->assertStatus(UtilityController::OK_STATUS)
            ->assertExactJson([UtilityController::MESSAGE_RESPONSE_KEY => $message]);
    }

    ############################################ Export To CSV #########################################################
    /** Tests an exported CSV file according to the headers that need to be checked against.
     * @param array $headersToBeChecked, the headers to be checked against the CSV file
     * @param string $url, specifies where to get the CSV file.
     * @param string $fileName, the name of the CSV file that is (assumed to be) downloaded.
     * */
    public function exportToCSV($headersToBeChecked, $url, $fileName){
        //when db is empty, expect for empty XML:
        $this->exportEmptyCSV($url,$fileName);
        //make a book in the database:
        $createResponse = $this->createABook($this->titles[1], [$this->authors[1]],[]);

        //construct the values to be checked against:
        $src = [Authors::ID_FIELD=> $createResponse[Authors::ID_FIELD][0],
            Authors::FIRSTNAME_FIELD=> $this->authors[1][Authors::FIRSTNAME_FIELD],
            Authors::LASTNAME_FIELD=> $this->authors[1][Authors::LASTNAME_FIELD],
            Books::TITLE_FIELD=> $this->titles[1],
            Books::ID_FIELD=> $createResponse[Books::ID_FIELD]];

        Excel::fake();
        //get the data:
        $response = $this->get($url);
        //assert OK status:
        $response->assertStatus(UtilityController::OK_STATUS);
        //check CSV content and check if it's downloaded or not:
        Excel::assertDownloaded($fileName, function(DBExport $export) use($src,$headersToBeChecked) {
            // loop through each header and check the values recorded in CSV file:
            foreach($headersToBeChecked as $header){
                //if the content is wrong, abort:
                if (! $export->collection()->contains($header, $src[$header] )){
                    return false;
                }
            }
            //all contents have been checked:
            return true;
        });
    }

    /** Tests whether an exported CSV file is empty or not.
     * @param string $url, specifies where to get the CSV file.
     * @param string $fileName, the name of the CSV file that is (assumed to be) downloaded.
     * */
    public function exportEmptyCSV($url,$fileName){
        Excel::fake();
        //get the data:
        $response = $this->get($url);
        //assert OK status:
        $response->assertStatus(UtilityController::OK_STATUS);
        //check CSV content and check if it's downloaded or not:
        Excel::assertDownloaded( $fileName, function(DBExport $export)  {
            // expect contents to be empty:
            return $export->collection()->isEmpty();
        });
    }
    ###################################### Search by Title / Author ####################################################
    /** Conducts tests for searching  by an author or a book's title:
     * 1. tests with valid request
     * 2. tests with non-exact request
     * 3. tests with invalid request
     * @param string $url, the url to send the search request to.
     */
    public function searchTestFacade($url){

        $createResponse =  $this->createABook($this->titles[0], [$this->authors[0]], [] );
        //combine all resources into 1 variable:
        $src = [Authors::ID_FIELD=> $createResponse[Authors::ID_FIELD][0],
            Authors::FIRSTNAME_FIELD=> $this->authors[0][Authors::FIRSTNAME_FIELD],
            Authors::LASTNAME_FIELD=> $this->authors[0][Authors::LASTNAME_FIELD],
            Books::TITLE_FIELD=> $this->titles[0],
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

    /** testing the search functionality with a valid title / author.
     * @param string $url, specifies where to send the search request to.
     * @param array $src, contains the search keyword (title, firstName and lastName).
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

    /** make sure search for a book only works with exact matches. this is tested by sending non-exact match requests.
     * @param string $url, the route to send the request to.
     * @param array $src, the source that has the exact matching values for the book's title or author.
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

    ############################################# Export To XML ########################################################

    /** checks the content of exported XML file.
     * @param array $headersToBeChecked, contains the headers to be checked against the XML file.
     * @param string $url, where to get the XML file from.
     * @param string $rootTag, expected XML's root element tag.
     * @param string $nestedTag, the tag nested within <data>.
     */
    public function exportToXML($headersToBeChecked, $url, $rootTag ,$nestedTag=""){
        //firstly, must create a book with an author:
        $createResponse =  $this->createABook($this->titles[2], [$this->authors[2]], [] );
        //now, get the xml output:
        $response = $this->get($url);
        //load it as an object:
        $object = simplexml_load_string($response->getContent());
        //check for a proper object type:
        $this->assertNotFalse($object);
        $this->assertInstanceOf( \SimpleXMLElement::class, $object);
        //combine all resources:
        $src = [ Authors::ID_FIELD=> $createResponse[Authors::ID_FIELD][0],
            Authors::FIRSTNAME_FIELD=> $this->authors[2][Authors::FIRSTNAME_FIELD],
            Authors::LASTNAME_FIELD=> $this->authors[2][Authors::LASTNAME_FIELD],
            Books::TITLE_FIELD=> $this->titles[2],
            Books::ID_FIELD=> $createResponse[Books::ID_FIELD]];
        /*
         * structure to be validated:
         * <?xml version="1.0"?>
            <authors>
              <data> --- (0)
                <ID>102</ID>
                <firstName>Hello</firstName>
                <lastName>World</lastName>
                <books> <----$nestedTag
                    <data> <--- checked in checkNestedXML()
                            ...
                    </data>
              </data>
            </authors> */

        //check the root tag <authors>:
        $this->assertTrue($object->getName() == $rootTag);
        //look for corresponding <data>:
        $this->assertObjectHasAttribute("data", $object);
        // check the values for child tags of (0):
        $childArray = $object->data;

       $this->checkNestedXML($headersToBeChecked,$childArray,$src,$nestedTag,$object->data);

    }

    /** check if the xml has a nested element, e.g. books under authors or vice versa:
     * @param array $headersToBeChecked, the headers to be checked against the XML file.
     * @param array $childArray, the elements under "<data> .. </data>" parsed as "[key1 => val1, key2 => val2, ...]".
     * @param array $src, contains the expected values of each header to be checked against the XML file.
     * @param string $nestedTag, the tag that indicates the XML has a nested element that needs to be checked again.
     * @param $xml, the xml file to be checked against.
     */
    public function checkNestedXML($headersToBeChecked,$childArray,$src,$nestedTag, $xml){
        //if no nesting, just loop to check the content under <data>:
        $this->validateXMLContent($headersToBeChecked[0],$childArray,$src );

        //if there is nesting, you must look for <data> again, and then run a loop to check its content:
        if ($xml->$nestedTag){
             //look for corresponding <data>:
            $this->assertObjectHasAttribute("data", $xml->$nestedTag);
            $grandChildArray = $xml->$nestedTag->data;
            //nesting only occurs once, so don't worry about recursion, just directly validate the $grandChildArray:
            $this->validateXMLContent($headersToBeChecked[1],$grandChildArray,$src );
        }
    }

    /** check that current xml elements under <data> are valid:
     * @param array $headersToBeChecked , the headers to be checked against in the XML file.
     * @param array $childArray, the elements under "<data> .. </data>" parsed as "[key1 => val1, key2 => val2, ...]".
     * @param array $src , contains the expected values of each header to be checked against the XML file.
     */
    public function validateXMLContent($headersToBeChecked,$childArray,$src){
        foreach ($headersToBeChecked as $header) {
                $this->assertTrue($src[$header] == $childArray->$header);
        }
    }
}
