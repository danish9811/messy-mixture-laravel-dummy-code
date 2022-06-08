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
        Schema::create('countries', function (Blueprint $table) {
            $table->engine = 'myIsam';
            $table->id();
            $table->string('name');             // "Afghanistan"
            $table->string('iso3');             // "AFG"
            $table->string('iso2');             // "AF"
            $table->string('numeric_code');     // "004"
            $table->string('phone_code');       // "93"
            $table->string('capital');          // "Kabul"
            $table->string('tld');              // ".af"
            $table->string('native');           // "افغانستان"
            $table->string('region');           // "Asia"
            $table->string('subregion');        // "Southern Asia"
            $table->float('latitude');
            $table->float('longitude');
            $table->string('flag')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('countries');
    }
};