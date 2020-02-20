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



/**
 *  adds a book to the database, along with its respective new/existing authors,
 * and authors are assigned to the book directly.
 */
Route::post('/api/books','PivotController@createNewBook'  );

/** deletes a book from the list.*/
Route::delete('/api/books','BooksController@destroy'  );

/** gets a list of books, with their authors. */
Route::get('/api/books', 'PivotController@index');

/** exports a list of books and/or authors to XML. */
Route::get('/api/books/export/XML', 'BooksController@exportToXML');
Route::get('/api/books/export/XML/with-authors','BooksController@exportToXML');
Route::get('/api/authors/export/XML','AuthorsController@exportToXML');
Route::get('/api/authors/export/XML/with-books','AuthorsController@exportToXML');

/** exports a list of books and/or authors to CSV. */
Route::get('/api/books/export/CSV','BooksController@exportToCSV');
Route::get('/api/authors/export/CSV/with-books','PivotController@exportToCSV');
Route::get('/api/authors/export/CSV','AuthorsController@exportToCSV');

/** change an author's name (firstName and lastName).*/
Route::put('/api/authors','AuthorsController@update'  );

/** gets an author's list of books, indicated by author's name details. */
Route::get('/api/authors/with-filter', 'PivotController@show');
/** gets a book, indicated by book title. */
Route::get('/api/books/with-filter', 'PivotController@show');
Route::get('/', function (){
    return view('welcome');
});
/** sends back error on unhandled routes*/
//code adapted from : https://laraveldaily.com/laravel-api-errors-and-exceptions-how-to-return-responses/
Route::fallback(function(){
    return response()->json([
        'message' => 'Page Not Found.'], 404);
});

