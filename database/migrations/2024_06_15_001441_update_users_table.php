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
            $table->string('name')->nullable()->change();
            $table->string('address', 255)->nullable();
            $table->string('identity_number', 15)->nullable();
            $table->timestamp('dob')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('avatar')->nullable();
            $table->string('phone_number', 15)->nullable();
            $table->string('otp')->nullable();
            $table->boolean('is_verify')->default(false);
            $table->boolean('is_activate')->default(false);
            $table->string('portrait')->nullable();
            $table->string('identity_image_front')->nullable();
            $table->string('identity_image_back')->nullable();
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
            $table->dropColumn('address');
            $table->dropColumn('identity_number');
            $table->dropColumn('dob');
            $table->dropColumn('gender');
            $table->dropColumn('avatar');
            $table->dropColumn('phone_number');
            $table->dropColumn('otp');
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('is_verify');
            $table->dropColumn('is_activate');
            $table->dropColumn('portrait');
            $table->dropColumn('identity_image_front');
            $table->dropColumn('identity_image_back');
        });
    }
};
