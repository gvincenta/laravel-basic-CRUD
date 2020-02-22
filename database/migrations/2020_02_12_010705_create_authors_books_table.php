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
            $table->increments('relationID');
             $table->integer(Authors::TABLE_NAME . '_'. Authors::ID_FIELD)->unsigned();
            $table->integer(Books::TABLE_NAME . '_'.Books::ID_FIELD)->unsigned();
            $table->foreign(Books::TABLE_NAME . '_'.Books::ID_FIELD)
                ->references(Books::ID_FIELD)->on(Books::TABLE_NAME)
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign(Authors::TABLE_NAME . '_'. Authors::ID_FIELD)
                ->references(Authors::ID_FIELD)->on(Authors::TABLE_NAME)
                ->onDelete('restrict')->onUpdate('cascade');

        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('authors_books');
    }
}
