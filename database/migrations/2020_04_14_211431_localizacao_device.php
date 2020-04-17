<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LocalizacaoDevice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tb_localizacao', function (Blueprint $table) {
            $table->bigInteger('idDevice')->unsigned();
            $table->foreign('idDevice')->references('idDevice')->on('tb_device')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tb_localizacao', function (Blueprint $table) {
            $table->dropColumn('idDevice');
        });
    }
}
