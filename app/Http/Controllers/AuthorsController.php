<?php

namespace App\Http\Controllers;

use App\Authors;
use App\Books;
use App\Exports\DBExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use XMLWriter;
use FetchLeo\LaravelXml\Facades\Xml;

class AuthorsController extends Controller
{
    private $export,$exportUtility;
    public function __construct()
    {
        $this->exportUtility = new ExportUtilityController();

    }
    //returns an author alongside his/her books.
    public function show($name){
        $result = Authors::with('books')->get();
        //TODO: change name to firstName and lastName
        $result->where("name","=","$name")->get();
        return $result->toJson();

    }
    public function update(Request $request){
        $validatedData = $request->validate([
            'firstName' => 'required',
            'lastName' => 'required',
            'ID' => 'required'
        ]);
        //TODO: FIND HOW TO CHANGE MULTIPLE VALS

        return json_encode(DB::update('UPDATE authors SET firstName = "' . $validatedData['firstName'] . '" WHERE ID =' . $validatedData['authorID']  ));
    }
    public function getSortedAuthors()
    {
        $result = Authors::with('books')->orderBy('lastName')->get();
        return $result->toJson();

    }


    public function exportToCSV(){
        $data = Authors::all();
        $this->export = new DBExport( $data , $this->exportUtility->extractHeadings($data));
        return $this->exportUtility->exportToCSV($this->export,'authors.csv');

    }

    //for exporting author only / with book titles to xml
    public function exportToXML(Request $request)
    {

        return $this->exportUtility->exportToXML(Authors::all(),[Authors::TABLE_NAME],[], [Authors::FIELDS],
         ExportUtilityController::XML_DATA_TAG);
    }

    /**
     * Store a newly created book in database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'firstName' => 'required',
            'lastName' => 'required',
            'ID' => 'required'
        ]);

        $author = new Authors;
        $author->firstName = $validatedData['firstName'];
        $author->lastName = $validatedData['lastName'];

        $author->save();


        return response()->json('author created!');
    }








}
