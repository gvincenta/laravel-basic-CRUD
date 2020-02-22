<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Books;
class CreateBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
          Schema::create(Books::TABLE_NAME, function (Blueprint $table) {
            $table->increments(Books::ID_FIELD);
            $table->string(Books::TITLE_FIELD);
          });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(Books::TABLE_NAME);
    }
}
