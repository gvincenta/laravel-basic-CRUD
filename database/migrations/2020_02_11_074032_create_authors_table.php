<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuthorsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create(Authors::TABLE_NAME, function (Blueprint $table) {
            $table->increments(Authors::ID_FIELD);
            $table->string(Authors::FIRSTNAME_FIELD);
             $table->string(Authors::LASTNAME_FIELD);

         });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(Authors::TABLE_NAME);
    }
}
