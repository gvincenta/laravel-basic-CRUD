<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuthorsBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('authors_books');

        Schema::create('authors_books', function (Blueprint $table) {
            $table->increments('ID');
             $table->integer('authors_ID')->unsigned();
            $table->integer('books_ID')->unsigned();
            $table->foreign('books_ID')
                ->references('ID')->on("books")
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('authors_ID')
                ->references('ID')->on("authors")
                ->onDelete('restrict')->onUpdate('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('authors_books');
    }
}
