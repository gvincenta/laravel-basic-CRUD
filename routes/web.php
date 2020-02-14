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


//TODO : using query parameters instead of request.body is still buggy.

/** gets a list of books with their authors. */
Route::get('/books', 'BooksController@index');

/**
 *  adds a book to the database, along with its respective new/existing authors,
 * and authors are assigned to the book directly.
 */
Route::post('/books','PivotController@createNewBook'  );

/** deletes a book from the list.*/
Route::delete('/books','BooksController@destroy'  );

/** gets a book by its title. */
Route::get('/books', 'BooksController@index');


/** gets a sorted list of books by its author. */
Route::get('/books/sorted/authors', 'AuthorsController@getSortedAuthors');

/** gets a sorted list of books by its title. */
Route::get('/books/sorted/titles', 'PivotController@getSortedBooks');

/** exports a list of books and/or authors to XML. */
Route::get('/books/export/XML', 'BooksController@exportToXML');
Route::get('/books/export/XML/with-authors','BooksController@exportToXML');
Route::get('/authors/export/XML','AuthorsController@exportToXML');
Route::get('/authors/export/XML/with-books','AuthorsController@exportToXML');

/** exports a list of books and/or authors to CSV. */
Route::get('/books/export/CSV','BooksController@exportToCSV');
Route::get('/authors/export/CSV/with-books','PivotController@exportToCSV');
Route::get('/authors/export/CSV','AuthorsController@exportToCSV');

/** gets a list of authors.*/
Route::get('/authors/', 'AuthorsController@index');

/** change an author's name (firstName and lastName).*/
Route::put('/authors','AuthorsController@update'  );

/** gets an author's list of books or a book, indicated by author's name details or book title. */
Route::get('/authors/with-filter', 'PivotController@show');
Route::get('/', function (){
    return view('welcome');
});
