<?php

namespace App\Http\Controllers;

use App\Authors;
use App\Books;
use App\Exports\DBExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use XMLWriter;
use FetchLeo\LaravelXml\Facades\Xml;

class AuthorsController extends Controller
{
    private $export,$exportUtility;
    public function __construct()
    {
        $this->exportUtility = new ExportUtilityController();

    }

    public function update(Request $request){

        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string',
            'lastName' => 'required|string',
            'ID' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            return ["message" => "invalid request", "code"=>400];
        }
        $updateData = ["firstName" => $request->input("firstName"),"lastName" => $request->input("lastName") ];
        $affectedRows = DB::table(Authors::TABLE_NAME)
            ->where('ID', '=',$request->input('ID'))
            ->update($updateData);
        return ["code" => 200, "affectedRows" => $affectedRows ];

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
        if(Str::contains($request->path(), PivotController::XML_AUTHORS_AND_BOOKS_PATH )){
            $results = Authors::with('books')->get();
            return $this->exportUtility->exportToXML($results,[PivotController::XML_AUTHORS_AND_BOOKS_PATH,Books::TABLE_NAME],
                [Books::TABLE_NAME], [Authors::FIELDS,Books::FIELDS], ExportUtilityController::XML_DATA_TAG);
        }

        return $this->exportUtility->exportToXML(Authors::all(),[Authors::TABLE_NAME],[], [Authors::FIELDS],
         ExportUtilityController::XML_DATA_TAG);
    }

    /**
     * Store a newly created author in database.
     *
     * @param array  $newAuthor, containing author's firstName and lastName
     * @return the id of the author
     */
    public function store($newAuthor)
    {
         return DB::table(Authors::TABLE_NAME)->insertGetId($newAuthor);
    }








}
