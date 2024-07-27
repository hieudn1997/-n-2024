<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_packages', function (Blueprint $table) {
            $table->unique('id')->change();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->bigInteger('amount')->unsigned();
            $table->integer('status')->unsigned();
            $table->bigInteger('user_package_id')->unsigned();
            $table->foreign('user_package_id')->references('id')->on('user_packages');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');

        Schema::table('user_packages', function (Blueprint $table) {
            $table->dropUnique('user_packages_id_unique');
        });
    }
};
