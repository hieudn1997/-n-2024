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
        Schema::create('user_package', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('owner_id')->nullable(false);
            $table->bigInteger('package_id')->nullable(false);
            $table->string('avatar')->nullable()->default(Config('package.default_avatar'));
            $table->decimal('investment_amount', 15, 3, true)->nullable()->default(0);
            $table->foreign('owner_id')->references('id')->on('users');
            $table->foreign('package_id')->references('id')->on('packages');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_package');
    }
};
