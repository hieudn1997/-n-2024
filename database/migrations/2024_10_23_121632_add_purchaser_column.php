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
        Schema::table('transactions', function (Blueprint $table) {
            $table->bigInteger('purchaser')->nullable();
        });

        Schema::table('fund_transactions', function (Blueprint $table) {
            $table->bigInteger('purchaser')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('purchaser');
        });

        Schema::table('fund_transactions', function (Blueprint $table) {
            $table->dropColumn('purchaser');
        });
    }
};
