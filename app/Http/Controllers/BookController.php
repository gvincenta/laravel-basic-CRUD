<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/** Controls book on database.
 *  A book has a title and an author.
*/
class BookController extends Controller
{
    //TODO: data management with DB instead of static list.
    private   $books;

    public function __construct() {
        $this->books = [];

        //populate data into static list:
        $data1 = array("author"=>"Hans Christian Andersen", "title"=>"Ugly Duckling" );
        $data2 = array("author"=>"William Shakespeare", "title"=>"Romeo And Juliet" );
        $data3 = array("author"=>"Kohei Horikoshi", "title"=>"My Hero Academia" );

        array_push($this->books,  $data1);
        array_push($this->books, $data2);
        array_push($this->books, $data3);

    }

    //Get a list of books.
    public function getBooks(){
        return $this->books;
    }

    //Get a list of books.
    //TODO : implement with database later.
    public function deleteBook(Request $request){
         return "data does not exist";
    }
    //Adds a book into the list.
    public function addBook(Request $request){
        //TODO : validate request format.
        array_push($this->books, $request->input());


        //TODO : handle success and failure scenarios.
        return $this->books;
    }
}
