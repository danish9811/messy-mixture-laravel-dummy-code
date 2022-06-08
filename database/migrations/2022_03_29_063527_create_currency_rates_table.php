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
        Schema::create('currency_rates', function (Blueprint $table) {
            $table->engine = 'myIsam';
            $table->id();
            $table->foreignId('currency_id')->references('id')->on('currencies');
            $table->string('source_crr');   // the source currency name "USD"
            $table->string('converted_crr');  // the converted currency name "PKR"
            $table->double('exchange_rate');   // exchange rate "200"
            $table->unsignedInteger('source_currency_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('currency_rates');
    }
};
