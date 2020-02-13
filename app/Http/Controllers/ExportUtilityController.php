<?php

namespace App\Http\Controllers;

 use App\Books;
 use App\Exports\PivotExport;
 use App\Exports\AuthorsExport;
 use App\Exports\BooksExport;
 use App\Exports\DBExport;
 use Illuminate\Http\Request;
 use Maatwebsite\Excel\Facades\Excel;
 use XMLWriter;

 /**
 * controls how to export to CSV and XML.
*/
class ExportUtilityController extends Controller
{
    public const XML_DATA_TAG = 'data';
    public const XML_BOOKS_WITH_AUTHORS = 'books-with-authors';
    public const XML_AUTHORS_WITH_BOOKS = 'authors-with-books';

    //extract the table's column names from an array of json.
    //code adapted from : https://stackoverflow.com/questions/10914687/retrieving-array-keys-from-json-input/32778117
    public function extractHeadings($exportData){
        $headings = [];
        if (count($exportData) > 0){
            $data = $exportData[0];
            foreach(json_decode($data) as $key => $val) {
                array_push($headings,$key);
            }
        }
        return $headings;
    }

     public function exportToCSV(  $export, $fileName )
    {
         return Excel::download($export, $fileName );
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
            ExportUtilityController::constructChild($xml,$childKeys,$attributes,1,$dataTag,$nestedTags, $json);

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
                ExportUtilityController::createXMLElement($xml,$json,$dataTag,$attributes[$counter-1], false );
                //init the child object:
                $xml->startElement($nestedTags[$counter]);
                //run through each child object, in case they have further child element(s) that needs to be parsed:
                foreach ($childObjects as $child){
                    ExportUtilityController::constructChild($xml,$childKeys,$attributes,$counter+1,$dataTag,$nestedTags, $child);
                }
                //close the child object:
                $xml->endElement();
                //close current data tag:
                $xml->endElement();
                return;


            }
        }
        //if we do not expect further child elements, parse the current data:
        ExportUtilityController::createXMLElement($xml,$json,$dataTag,$attributes[$counter-1], true );



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
