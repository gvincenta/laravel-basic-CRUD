<?php


namespace App\Exports;


use App\Authors;
use App\Books;
use Illuminate\Support\Facades\DB;

class AuthorsExport extends DBExport
{
    public function __construct()
    {
        parent::__construct();

    }
    public function retrieveExportData()
    {
        return Authors::all();
    }
}
