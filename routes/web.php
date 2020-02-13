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
Route::get('/export/XML/books-and-authors','PivotController@exportToXML');
Route::get('/export/XML/authors-and-books','PivotController@exportToXML');

/** exports a list of books and/or authors to CSV. */
Route::get('/export/CSV/authors-and-books','PivotController@exportToCSV');
Route::get('/export/CSV/authors','AuthorsController@exportToCSV');
Route::get('/export/CSV/books','BooksController@exportToCSV');


/**
 *  adds a book to the list.
 */
Route::post('/books','PivotController@store'  );
/**
 * stores a new author.
*/
Route::post('/author', 'AuthorsController@store');

/**
 *  deletes a book from the list.
 */
Route::delete('/book','BooksController@destroy'  );
/**
 *  change an author's name (firstName and lastName).
 */
Route::put('/author','AuthorsController@update'  );
/**
 *  gets a sorted list of books by its author.
 */
Route::get('/book/sorted/author', 'AuthorsController@getSortedAuthors');
/**
 *  gets a sorted list of books by its title.
 */
Route::get('/book/sorted/title', 'BooksController@getSortedBooks');
/**
 *  gets an author's list of books.
 */
Route::get('/author/', 'AuthorsController@show');
/**
 *  gets a book by its title.
 */
Route::get('/book', 'BooksController@index');
