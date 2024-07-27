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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('portrait');
            $table->string('identity_image_front_hash')->nullable();
            $table->string('identity_image_back_hash')->nullable();
            $table->unique('identity_number', 'identity_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('portrait')->nullable();
            $table->dropColumn('identity_image_front_hash');
            $table->dropColumn('identity_image_back_hash');
            $table->dropUnique('identity_number');
        });
    }
};
