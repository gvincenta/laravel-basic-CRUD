<?php

namespace App\Exports;


use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;


   class DBExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    private $headings =[];
    private $exportData = []; //array of json as extracted from database.

    //by default, export data and set headings directly.
       public function __construct($exportData,$headings)
       {
           //extract columns from query:
           $this->setHeadings( $headings );
           //get data collection from query:
           $this->setExportData(
               $exportData
           );
       }

     public function collection()
    {
        return $this->getExportData();
    }


    //implements WithHeadings interface:
    public function headings():array
    {
        return $this->headings;
    }
    //setter for headings:
    public function setHeadings($headings )
    {
        $this->headings = $headings;
    }

     /* getter setter for exportData: */
     public function getExportData(){
        return $this->exportData;
     }
     public function setExportData($exportData){
        $this->exportData = $exportData;
     }
}
