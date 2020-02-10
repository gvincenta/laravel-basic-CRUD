<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthorController extends Controller
{
    //TODO: data management with DB instead of static list.
    private $authors;

    public function __construct() {
        $this->authors = [];

        //populate data into static list:

        array_push($this->authors,  "Gilbert");
        array_push($this->authors, "Hans");

    }
    public function changeName(Request $request){
        $len = count($this->authors);
        for ($i = 0; $i < $len; $i++){
            if ($this->authors[$i] == $request->input("author_old_name")){
                $this->authors[$i] =$request->input("author_new_name");
                return "success";
            }
        }
        return "author does not exist";
    }
}
