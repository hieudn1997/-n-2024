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
        Schema::create('fund_transactions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('ref');
            $table->float('amount');
            $table->tinyInteger('status');
            $table->tinyInteger('type');
            $table->bigInteger('user_asset_id');
            $table->foreign('user_asset_id')->references('id')->on('user_assets');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fund_transactions');
    }
};
