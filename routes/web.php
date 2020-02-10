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
 *  gets a list of books.
 */
Route::get('/book', 'BookController@getBooks');

/**
 *  adds a book to the list.
 */
Route::post('/book','BookController@addBook'  );

/**
 *  deletes a book from the list.
 */
Route::delete('/book','BookController@deleteBook'  );

/**
 *  change an author's name.
 */
Route::put('/author','AuthorController@changeName'  );


