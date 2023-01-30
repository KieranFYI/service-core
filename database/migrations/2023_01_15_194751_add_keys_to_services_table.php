<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->binary('asymmetric_key')
                ->after('key')
                ->nullable();
            $table->binary('symmetric_key')
                ->after('asymmetric_key')
                ->nullable();
            $table->string('endpoint')
                ->after('symmetric_key')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['asymmetric_key', 'endpoint']);
        });
    }
};
