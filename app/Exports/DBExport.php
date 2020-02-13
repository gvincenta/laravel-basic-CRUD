<?php

namespace App\Exports;

 use App\Authors;
 use App\Books;
 use Illuminate\Support\Facades\DB;
 use Maatwebsite\Excel\Concerns\FromCollection;
 use Maatwebsite\Excel\Concerns\WithHeadings;
 use Maatwebsite\Excel\Concerns\Exportable;

 abstract class DBExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    private $headings =[];
    private $exportData = []; //array of json as extracted from database.

    //by default, export data and set headings directly.
    public function __construct()
    {
        $this->setExportData(
            $this->retrieveExportData()
        );
        $this->setHeadings(
            $this->extractKeys()
        );

    }

     public function collection()
    {
        return $this->getExportData();

    }
    //retrieves data from database.
     abstract protected function retrieveExportData();


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

     //extract the table's column names from an array of json.
     //code adapted from : https://stackoverflow.com/questions/10914687/retrieving-array-keys-from-json-input/32778117
     public function extractKeys(){
         $columns = [];
         if (count($this->exportData) > 0){
             $data = $this->exportData[0];
             foreach(json_decode($data) as $key => $val) {
                 array_push($columns,$key);
             }
         }
          return $columns;
     }
     /* getter setter for exportData: */
     public function getExportData(){
        return $this->exportData;
     }
     public function setExportData($exportData){
        $this->exportData = $exportData;
     }
}
