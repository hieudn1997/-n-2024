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
            $table->string('issue_place')->nullable();
            $table->string('issue_date')->nullable();
            $table->string('valid_date')->nullable();
            $table->dropColumn('gender');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->enum('gender', ['Nam', 'Nữ', 'Khác'])->nullable();
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
            $table->dropColumn('issue_place');
            $table->dropColumn('issue_date');
            $table->dropColumn('valid_date');
            $table->dropColumn('gender');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
        });
    }
};
