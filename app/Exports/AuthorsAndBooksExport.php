<?php


namespace App\Exports;


use App\Authors;
use App\Books;
use Illuminate\Support\Facades\DB;

class AuthorsAndBooksExport extends DBExport
{
    //query return type is different from default and has its own columns attribute, so override the constructor:
    public function __construct()
    {
        //run query:
        $query = $this->retrieveExportData();
        //extract columns from query:
        $this->setHeadings( $query->columns );
        //get data collection from query:
        $this->setExportData(
            $query->get()
        );
    }
    public function retrieveExportData()
    {
        return DB::table('authors_books')
            ->rightJoin(Authors::TABLE_NAME, 'authors.authorID', '=', 'authors_books.authors_authorID')
            ->leftJoin(Books::TABLE_NAME, 'books.bookID', '=', 'authors_books.books_bookID')
            ->select('authors.authorID', 'authors.name', 'books.bookID', 'books.title');
    }

}
