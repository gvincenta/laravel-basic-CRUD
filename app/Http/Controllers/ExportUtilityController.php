<?php

namespace App\Http\Controllers;

use Maatwebsite\Excel\Facades\Excel;
use XMLWriter;

 /**
 * Controls how to export to CSV and XML.
*/
 //TODO : proper documentation of how exporting works.
class ExportUtilityController extends Controller
{
    public const XML_DATA_TAG = 'data';
    public const INVALID_REQUEST_MESSAGE = "invalid request";
    public const INVALID_REQUEST_STATUS = 400;
    public const OK_STATUS = 200;

    /**
     * extract the table's column names from an array of json.
     * @param Array $exportData, array of json to be extracted.
     */
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

    /**
     * converts an array of array/JSON into XML file.
     * @param XMLWriter $xml, the xml writer object.
     * @param Array $array, the array of array/JSON to be converted.
     * @param Array $childKeys, where the child objects to be added to XML are located in the array.
     * @param Array $attributes, the attributes to be parsed into the XML.
     * @param String $dataTag, the tag for the data to be parsed.
     */
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
    /**
     * recursively runs through a JSON object to detect if it has a child (in an array of JSON object format)
     * that needs to be parsed into separate XML tag.
     * @param XMLWriter $xml, the xml writer object.
     * @param Array $childKeys, where the child objects to be added to XML are located in the array.
     * @param Array $attributes, the attributes to be parsed into the XML.
     * @param String $dataTag, the tag for the data to be parsed.
     * @param Array $nestedTags, contains root element tag, with its children tags as well.
     * @param Array $json, the current element to be inspected for possible nesting children.
     */
    private function constructChild($xml, $childKeys, $attributes, $counter, $dataTag, $nestedTags, $json)
    {
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
    /**
     * parse a data (e.g. a book or an author) with all of its attributes.
     * @param XMLWriter $xml, the xml writer object.
     * @param Array $data,  data that needs to be parsed to XML.
     * @param String $tag, the XML tag for the data.
     * @param Array $attributes, the attributes of the data to be parsed.
     * @param Boolean $closeTag, whether the tag is immediately closed or not.
     */
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
