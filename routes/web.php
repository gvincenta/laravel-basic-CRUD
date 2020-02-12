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

Route::put('/', function () {
//    return view('welcome');
    return "hello world";
});

/**
 *  gets a list of books with their authors.
 */
Route::get('/book/list', 'BookController@getBooks');




////TODO : using query parameters instead of request.body is still buggy.
//Route::get('/author','AuthorController@getAuthor'  );


/**
 *  exports a list of books and/or authors to csv and/or xml.
 */
Route::get('/export', function (){
    //TODO: to be implemented with mySQL.
    return "unimplemented";
});
/**
 *  adds a book to the list.
 */
Route::post('/book','BookController@store'  );
/**
 * stores a new author.
*/
Route::post('/author', 'AuthorController@store');

/**
 *  deletes a book from the list.
 */
Route::delete('/book','BookController@destroy'  );
/**
 *  change an author's name.
 */
Route::put('/author','AuthorController@update'  );
/**
 *  gets a sorted list of books by its author.
 */
Route::get('/book/sorted/author', 'AuthorController@getSortedAuthors');
/**
 *  gets a sorted list of books by its title.
 */
Route::get('/book/sorted/title', 'BookController@getSortedBooks');
/**
 *  gets an author's list of books.
 */
Route::get('/author/', 'AuthorController@show');
/**
 *  gets a book by its title.
 */
Route::get('/book', 'BookController@index');
