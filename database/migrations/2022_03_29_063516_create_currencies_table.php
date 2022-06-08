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
        Schema::create('currencies', function (Blueprint $table) {
            $table->engine = 'myIsam';
            $table->id();
            $table->string('currency');         // "AFN"
            $table->string('name');    // "Afghan Afghani"
            $table->string('symbol');  // "؋"
            $table->foreignId('country_id')
                ->unique()
                ->references('id')
                ->on('countries');
            $table->unsignedDouble('latest_rate')->nullable();  // because exchagne rate of some countries does not include in response
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('currencies');
    }
};
