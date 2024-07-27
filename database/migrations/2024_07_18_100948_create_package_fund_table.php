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
        Schema::create('package_fund', function (Blueprint $table) {
            $table->id();
            $table->integer('package_id')->unsigned()->index();
            $table->foreign('package_id')->references('id')->on('packages')->onDelete('cascade');
            $table->integer('fund_id')->unsigned()->index();
            $table->foreign('fund_id')->references('id')->on('funds')->onDelete('cascade');
            $table->integer('allocation_percentage')->unsigned()->index();
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
        Schema::dropIfExists('package_fund');
    }
};
