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
        Schema::table('packages', function (Blueprint $table) {
            $table->boolean('is_default')->after('name')->default(false);
            $table->dropColumn('investment_amount');
            $table->dropColumn('owner_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('is_default');
            $table->double('investment_amount')->nullable();
            $table->bigInteger('owner_id')->unsigned()->nullable();
            $table->foreign('owner_id')->references('id')->on('users');
        });
    }
};
