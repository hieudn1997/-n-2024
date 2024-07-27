<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::table('funds', function (Blueprint $table) {
            $table->dropForeign(['credential_id']);
        });

        Schema::table('fund_credentials', function (Blueprint $table) {
            // $table->dropPrimary('id');
            $table->renameColumn('id', 'key');
        });

        Schema::table('fund_credentials', function (Blueprint $table) {
            $table->text('token')->nullable()->change();
            $table->integer('expired_at')->nullable();
            $table->id();
        });

        DB::statement('ALTER TABLE funds ALTER COLUMN credential_id TYPE integer USING 1');

        Schema::table('funds', function (Blueprint $table) {
            $table
                ->foreign('credential_id')
                ->references('id')
                ->on('fund_credentials')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });

        Schema::rename('fund_credentials', 'credentials');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('credentials', 'fund_credentials');

        Schema::table('funds', function (Blueprint $table) {
            $table->dropForeign(['credential_id']);
        });

        Schema::table('fund_credentials', function (Blueprint $table) {
            $table->dropPrimary('id');
            $table->dropColumn('id');
            $table->dropColumn('expired_at');
        });

        Schema::table('fund_credentials', function (Blueprint $table) {
            $table->string('token')->nullable()->change();
            $table->renameColumn('key', 'id');
            $table->primary('id');
        });

        Schema::table('funds', function (Blueprint $table) {
            $table->string('credential_id')->change();
        });

        $credential = DB::table('fund_credentials')->select('id')->first();

        DB::table('funds')->update(["credential_id" => $credential?->id]);

        Schema::table('funds', function (Blueprint $table) {
            $table
                ->foreign('credential_id')
                ->references('id')
                ->on('fund_credentials')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }
};
