<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\AuthorsController;
use App\Http\Controllers\BooksController;
use Illuminate\Http\Request;

Route::put('/', function () {
//    return view('welcome');
    return "hello world";
});

/**
 *  gets a list of books with their authors.
 */
Route::get('/book/list', 'BooksController@getBooks');


////TODO : using query parameters instead of request.body is still buggy.
//Route::get('/author','AuthorsController@getAuthor'  );


/** exports a list of books and/or authors to XML. */
Route::get('/export/XML/books', 'BooksController@exportToXML');
Route::get('/export/XML/authors','AuthorsController@exportToXML');
Route::get('/export/XML/books-and-authors','BooksController@exportToXML');
Route::get('/export/XML/authors-and-books','AuthorsController@exportToXML');

/** exports a list of books and/or authors to CSV. */
Route::get('/export/CSV/authors-and-books','PivotController@exportToCSV');
Route::get('/export/CSV/authors','AuthorsController@exportToCSV');
Route::get('/export/CSV/books','BooksController@exportToCSV');


/**
 *  adds a book to the database, along with its respective new/existing authors,
 * and authors are assigned to the book directly.
 */
Route::post('/books','PivotController@createNewBook'  );

/**
 *  deletes a book from the list.
 */
Route::delete('/books','BooksController@destroy'  );
/**
 *  change an author's name (firstName and lastName).
 */
Route::put('/authors','AuthorsController@update'  );
/**
 *  gets a sorted list of books by its author.
 */
Route::get('/books/sorted/authors', 'AuthorsController@getSortedAuthors');
/**
 *  gets a sorted list of books by its title.
 */
Route::get('/books/sorted/titles', 'PivotController@getSortedBooks');
/**
 *  gets an author's list of books.
 */
Route::get('/authors', 'PivotController@show');
/**
 *  gets a book by its title.
 */
Route::get('/books', 'BooksController@index');
