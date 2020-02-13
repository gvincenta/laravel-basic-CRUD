<?php

namespace App\Http\Controllers;

 use App\Books;
 use App\Exports\AuthorsAndBooksExport;
 use App\Exports\AuthorsExport;
 use App\Exports\BooksExport;
 use App\Exports\DBExport;
 use Illuminate\Http\Request;
 use Maatwebsite\Excel\Facades\Excel;
 /**
 * handles exporting database files into CSV and XML.
*/
class FileExportController extends Controller
{
    public const XML_DATA_TAG = 'data';
    public const XML_BOOKS_WITH_AUTHORS = 'books-with-authors';
    public const XML_AUTHORS_WITH_BOOKS = 'authors-with-books';
    private $export = null;

    public function decideExportClass($titles, $authors){

        if ($titles && $authors  ){
            $this->export = new AuthorsAndBooksExport();
        }
        else if (!$titles && $authors){
            $this->export = new AuthorsExport();
        }
        else if ($titles && !$authors){
            $this->export = new BooksExport();
        }
    }
     public function exportToCSV(Request $request)
    {
        $validatedData = $request->validate([
            'titles' => 'required',
            'authors' => 'required'
        ]);
        $this->decideExportClass($validatedData['titles'],$validatedData['authors']);
        $data = Excel::download($this->export, 'disney.csv' );
        return $data;

    }


    //function adapted from : https://stackoverflow.com/questions/30014960/how-can-i-store-data-from-mysql-to-xml-in-laravel-5
    public function exportToXML($array, $nestedTags, $childKeys, $attributes, $dataTag)
    {
        //init xml with parent tag:
        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->startDocument();
        $xml->startElement($nestedTags[0]);

        //loop through each json item in the array:
        foreach($array as $json) {
            FileExportController::constructChild($xml,$childKeys,$attributes,1,$dataTag,$nestedTags, $json);

        }

        //encloses the xml tags and return it:

        $xml->endElement();
        $xml->endDocument();

        $content = $xml->outputMemory();
        $xml = null;

        return response($content)->header('Content-Type', 'text/xml');
    }
    /* recursively runs through a JSON object to detect if it has a child (in an array of JSON object format)
     * that needs to be parsed into separate XML tag.
     */
    private function constructChild($xml, $childKeys, $attributes, $counter, $dataTag, $nestedTags, $json){
        if (count($childKeys) > ($counter-1) ){
            $childKey = $childKeys[$counter-1];
            $childObjects = $json->$childKey;
            //if we have child objects that needs to be parsed:
            if (count($childObjects) >0){
                //firstly, parse the current data, but don't close it yet:
                FileExportController::createXMLElement($xml,$json,$dataTag,$attributes[$counter-1], false );
                //init the child object:
                $xml->startElement($nestedTags[$counter]);
                //run through each child object, in case they have further child element(s) that needs to be parsed:
                foreach ($childObjects as $child){
                    FileExportController::constructChild($xml,$childKeys,$attributes,$counter+1,$dataTag,$nestedTags, $child);
                }
                //close the child object:
                $xml->endElement();
                //close current data tag:
                $xml->endElement();
                return;


            }
        }
        //if we do not expect further child elements, parse the current data:
        FileExportController::createXMLElement($xml,$json,$dataTag,$attributes[$counter-1], true );



    }
    //parse a data with all of its attributes:
    private function createXMLElement($xml,$data,$tag,$attributes, $closeTag)
    {
        $xml->startElement($tag);

        //parse each attribute of the data as a new element:
        foreach($attributes as $attribute) {
            $xml->writeElement($attribute,$data->$attribute);
        }
        if ($closeTag){
            $xml->endElement();

        }

    }
}
