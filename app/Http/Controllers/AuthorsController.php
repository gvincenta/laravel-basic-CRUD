<?php

namespace App\Http\Controllers;

use App\Authors;
use App\Books;
use App\Exports\DBExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use XMLWriter;

class AuthorsController extends Controller
{
    private $export,$utility;
    public const XML_AUTHORS_AND_BOOKS_PATH = "with-books";
    public const AUTHORS_EXPORT_CSV_FILENAME  = 'authors.csv';
    public const CHANGE_NAME_SUCCEED_MESSAGE = "changing name succeed";
    public const CHANGE_NAME_FAILED_MESSAGE = "changing name failed";
    public function __construct()
    {
        $this->utility = new UtilityController();
    }

    /**
     * Updates an author's firstName and lastName.
     * @param  \Illuminate\Http\Request  $request, containing ID of the author to be updated.
     * @return \Illuminate\Http\JsonResponse, the number of rows deleted, if request is valid.
     * @return \Illuminate\Http\JsonResponse, invalid request, if request is invalid.
     */
    public function update(Request $request){

        $validator = Validator::make($request->all(), [
            Authors::FIRSTNAME_FIELD => 'required|string',
            Authors::LASTNAME_FIELD => 'required|string',
            Authors::ID_FIELD => 'required|numeric'
        ]);
        if ($validator->fails()) {
            return  response()->json([UtilityController::MESSAGE_RESPONSE_KEY  =>UtilityController::INVALID_REQUEST_MESSAGE],
                UtilityController::INVALID_REQUEST_STATUS);
        }
        $updateData = [Authors::FIRSTNAME_FIELD => $request->input(Authors::FIRSTNAME_FIELD),
            Authors::LASTNAME_FIELD => $request->input(Authors::LASTNAME_FIELD) ];
        $affectedRows = DB::table(Authors::TABLE_NAME)
            ->where(Authors::ID_FIELD, '=',$request->input(Authors::ID_FIELD))
            ->update($updateData);

        //for completed update:
        if ($affectedRows == 1){
            return  response()->json([UtilityController::MESSAGE_RESPONSE_KEY => self::CHANGE_NAME_SUCCEED_MESSAGE ],
                UtilityController::OK_STATUS);
        //for failed update (i.e. no rows affected):
        }else{
            return  response()->json([UtilityController::MESSAGE_RESPONSE_KEY  => self::CHANGE_NAME_FAILED_MESSAGE],
                UtilityController::OK_STATUS);
        }

    }

    /**
     * Exports all authors to csv file.
     * @return , CSV file.
     */
    public function exportToCSV()
    {
        $data = Authors::all();
        $this->export = new DBExport( $data , $this->utility->extractHeadings($data));
        return $this->utility->exportToCSV($this->export,self::AUTHORS_EXPORT_CSV_FILENAME);
    }

    /**
     * for exporting author only / with book titles from database to XML file.
      * @param  Illuminate\Http\Request $request, containing the URL to identify which file is wanted.
     * @return , the CSV file requested.
     */
    public function exportToXML(Request $request)
    {
        //exports all authors, along with their books, from database to XML file:
        if(Str::contains($request->path(), AuthorsController::XML_AUTHORS_AND_BOOKS_PATH )){
            $results = Authors::with(Books::TABLE_NAME)->get();
            return $this->utility->exportToXML($results,[Authors::TABLE_NAME,Books::TABLE_NAME],
                [Books::TABLE_NAME], [Authors::FIELDS,Books::FIELDS], UtilityController::XML_DATA_TAG);
        }
        //exports all authors from database  to XML file:
        return $this->utility->exportToXML(Authors::all(),[Authors::TABLE_NAME],[], [Authors::FIELDS],
         UtilityController::XML_DATA_TAG);
    }

    /**
     * Store a newly created author in database.
     * @param array  $newAuthor, containing author's firstName and lastName
     * @return integer, the id of the author
     */
    public function store($newAuthor)
    {
        //extract only author's firstName and lastName:
        $authorData = [ Authors::FIRSTNAME_FIELD => $newAuthor[Authors::FIRSTNAME_FIELD],
            Authors::LASTNAME_FIELD =>$newAuthor[Authors::LASTNAME_FIELD ]];
         return DB::table(Authors::TABLE_NAME)->insertGetId($authorData);
    }
}
