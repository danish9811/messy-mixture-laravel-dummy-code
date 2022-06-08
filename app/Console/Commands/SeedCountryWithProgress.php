<?php

// THIS COUNTRY SEEDER WITH PROGRESS BAR IS WORKING NICELY

namespace App\Console\Commands;

use App\Models\Country;
use Illuminate\Console\Command;

class SeedCountryWithProgress extends Command {

    protected $signature = 'seed:country';

    protected $description = 'Command description';

    public function handle() {

//         COUNTRY SEEDER STARTED
        $countriesJson = json_decode(file_get_contents(database_path('jsons/countries.json')), false, 512, JSON_THROW_ON_ERROR);

        $countryBar = $this->output->createProgressBar(count($countriesJson));

        $this->line('   seeding countries table');
        $countryBar->start();

        foreach ($countriesJson as $country) {
            Country::updateOrCreate(
                ['name' => $country->name], [
                'name' => $country->name, // Afghanistan
                'iso3' => $country->iso3, // AFG
                'iso2' => $country->iso2, // af
                'numeric_code' => $country->numeric_code, // 004
                'phone_code' => $country->phone_code, // +93
                'capital' => $country->capital,   // Kabul
                'tld' => $country->tld,   //  .af
                'native' => $country->native, // افغانستان
                'region' => $country->region, // Asia
                'subregion' => $country->subregion,   // southern Asia
                'latitude' => $country->latitude,
                'longitude' => $country->longitude,
                'flag' => $country->flag
            ]);
            $countryBar->advance();
        }

        $countryBar->finish();
        $this->newLine();
        $this->newLine(1);
//         COUNTRY SEEDER FINISHED  -------------------------------

        return 0;
    }
}
