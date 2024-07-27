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
        Schema::table('user_package', function (Blueprint $table) {
            $table->string('avatar')->default(null)->change();
            $table->dropPrimary();
            $table->primary(['user_id', 'package_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_package', function (Blueprint $table) {
            $table->string('avatar')->default(Config('package.default_avatar'))->change();
            $table->dropPrimary(['user_id', 'package_id']);
            $table->primary('id');
        });
    }
};
