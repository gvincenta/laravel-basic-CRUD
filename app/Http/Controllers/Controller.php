<?php

namespace App\Http\Controllers;

use App\Books;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use XMLWriter;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    protected function exportToXMLHelper($array, $nestedTags, $childKeys, $attributes, $dataTag)
    {
        //init xml with parent tag:
        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->startDocument();
        $xml->startElement($nestedTags[0]);

         //loop through each json item in the array:
        foreach($array as $json) {
            Controller::constructChild($xml,$childKeys,$attributes,1,$dataTag,$nestedTags, $json);

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
                Controller::createXMLElement($xml,$json,$dataTag,$attributes[$counter-1], false );
                //init the child object:
                $xml->startElement($nestedTags[$counter]);
                //run through each child object, in case they have further child element(s) that needs to be parsed:
                foreach ($childObjects as $child){
                    Controller::constructChild($xml,$childKeys,$attributes,$counter+1,$dataTag,$nestedTags, $child);
                }
                //close the child object:
                $xml->endElement();
                //close current data tag:
                $xml->endElement();
                return;


            }
        }
        //if we do not expect further child elements, parse the current data:
        Controller::createXMLElement($xml,$json,$dataTag,$attributes[$counter-1], true );



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
