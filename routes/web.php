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
 *  gets a book alongside its author.
 */
Route::get('/book', 'BookController@getBook');
/**
 *  gets a list of books with their authors.
 */
Route::get('/book/list', 'BookController@getBooks');
/**
 *  gets a sorted list of books by its title.
 */
Route::get('/book/sorted/title', 'BookController@getSortedBooks');
/**
 *  gets a sorted list of books by its author.
 */
Route::get('/book/sorted/author', 'AuthorController@getSortedAuthors');
/**
 *  adds a book to the list.
 */
Route::post('/book','BookController@addBook'  );

/**
 *  deletes a book from the list.
 */
Route::delete('/book','BookController@deleteBook'  );
/**
 *  get an author, alongside its books.
 */
//TODO : using query parameters instead of request.body is still buggy.
Route::get('/author','AuthorController@getAuthor'  );
/**
 *  change an author's name.
 */
Route::put('/author','AuthorController@changeName'  );

/**
 *  exports a list of books and/or authors to csv and/or xml.
 */
Route::get('/export', function (){
    //TODO: to be implemented with mySQL.
    return "unimplemented";
});

