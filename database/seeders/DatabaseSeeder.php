<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\Currency;
use App\Models\State;
use App\Models\Timezone;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run() {

        // \App\Models\User::factory(5)->create();  // UserFactory::class
        $this->call([
//            CountrySeeder::class,
//            TimezoneSeeder::class,
//            StateSeeder::class,
            CurrencySeeder::class,
//            CitySeeder::class
        ]);

    }

}
