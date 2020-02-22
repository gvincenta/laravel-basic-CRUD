<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Books;
use App\Authors;
use App\Http\Controllers\PivotController;
class CreateAuthorsBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create(PivotController::TABLE_NAME, function (Blueprint $table) {
            $table->increments(PivotController::ID_FIELD);
             $table->integer(PivotController::AUTHORS_ID_FIELD)->unsigned();
            $table->integer(PivotController::BOOKS_ID_FIELD)->unsigned();
            $table->foreign( PivotController::BOOKS_ID_FIELD)
                ->references(Books::ID_FIELD)->on(Books::TABLE_NAME)
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign(PivotController::AUTHORS_ID_FIELD)
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
        Schema::dropIfExists(PivotController::TABLE_NAME);
    }
}
