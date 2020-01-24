<?php
/**
 * @package     IdnPlay\Vock88\Migration - CreateVock88Table
 * @author      singkek
 * @copyright   Copyright(c) 2019
 * @version     1
 * @created     2020-01-24
 * @updated     2020-01-24
 **/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVock88Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(env('VOCK88_STORAGE_KEY', 'z_vock88_token'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('access_token');
            $table->dateTime('expired');
            $table->dateTime('created');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(env('VOCK88_STORAGE_KEY', 'z_vock88_token'));
    }
}
