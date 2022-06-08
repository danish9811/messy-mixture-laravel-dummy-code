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
    public function up() {
        Schema::create('states', function (Blueprint $table) {
            $table->engine = 'myIsam';
            $table->id();
            $table->string('name');             // state name like "badakhshan"
            $table->string('state_code');       // state code like "BDS" for badakhshan
            $table->foreignId('country_id')->references('id')->on('countries');
            $table->float('latitude')->nullable();
            $table->float('longitude')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('states');
    }
};
