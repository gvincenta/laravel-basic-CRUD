<?php

namespace App\Exports;


use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Class DBExport
 * @package App\Exports
 * An adapter class that communicates with Maatwebsite\Excel\... components when exporting data to CSV.
 */
class DBExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    private $headings =[];
    private $exportData = []; //array of json as extracted from database.

       /** by default, set export data and set headings directly.
        * DBExport constructor.
        * @param $exportData
        * @param $headings
        */
       public function __construct($exportData,$headings)
       {
           //get data headings from query:
           $this->setHeadings( $headings );
           //get data collection from query:
           $this->setExportData(  $exportData );
       }

     public function collection()
    {
        return $this->getExportData();
    }


       /** implements WithHeadings interface.
        * @return array, the headings / columns to be exported to CSV file.
        */
    public function headings():array
    {
        return $this->headings;
    }

       /** setter for headings.
        * @param $headings
        */
    public function setHeadings($headings )
    {
        $this->headings = $headings;
    }

       /** getter for exportData.
        * @return array, the data to be exported.
        */
     public function getExportData(){
        return $this->exportData;
     }

       /** setter for exportData.
        * @param $exportData
        */
     public function setExportData($exportData){
        $this->exportData = $exportData;
     }
}
