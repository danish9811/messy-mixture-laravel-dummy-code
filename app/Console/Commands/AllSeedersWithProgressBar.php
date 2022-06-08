<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\Currency;
use App\Models\State;
use App\Models\Timezone;
use Illuminate\Console\Command;

class AllSeedersWithProgressBar extends Command {

    protected $signature = 'start:filling';
    protected $description = 'Command description';

    public function handle() {

        $this->info('Please don\'t close this terminal while data is being seeded');

        // country seeder ends here
        $countriesJson = json_decode(file_get_contents(database_path('jsons/countries.json')), false, 512, JSON_THROW_ON_ERROR);

        $countryBar = $this->output->createProgressBar(count($countriesJson));

        $this->line('  Seeding countries table');
        $this->newLine();
        $countryBar->start();

        foreach ($countriesJson as $country) {
            Country::updateOrCreate([
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
        $this->line('countries table filled');
        $this->newLine(1);
        // --- country seeder ends here

        // -------------------- CURRENCY DATA ----------------------------------------
        $currenciesData = [];
        if (($open = fopen(storage_path('newCsvFiles/countries.csv'), "r + b")) !== FALSE) {
            while (($singleRecord = fgetcsv($open, NULL, ",")) !== FALSE) {
                $currenciesData[] = $singleRecord;
            }
            fclose($open);
        }

        $countCurrencies = count($currenciesData);
        $currencyBar = $this->output->createProgressBar($countCurrencies);

        $this->line('Currencies table filling started');
        $this->newLine();
        $currencyBar->start();

        array_shift($currenciesData);
        foreach ($currenciesData as $key => $value) {
            $insertArray[$key] = [
                'currency' => $value[7],
                'name' => $value[8],
                'symbol' => $value[9],
                'country_id' => Country::where('iso2', $value[3])->first()['id']
            ];
            $currencyBar->advance();
        }

        Currency::upsert($insertArray, [
            'country_id'
        ]);

        $currencyBar->finish();
        $this->newLine();
        $this->line('Filling currencies table completed');
        $this->newLine(1);

        // currency data filling ended here

        // --------------- running now states seeders here
        $states = json_decode(file_get_contents(database_path('jsons/states.json')));

        $statesCount = count($states);

        $stateBar = $this->output->createProgressBar($statesCount);
        $this->line('starting state seeding');
        $this->newLine();
        $stateBar->start();

        foreach ($states as $state) {

            State::updateOrCreate([
                'name' => $state->name,
                'state_code' => $state->state_code
            ],
                [
                    'name' => $state->name,
                    'state_code' => $state->state_code,
                    'country_id' => Country::where('iso2', $state->country_code)->first()['id'],
                    'latitude' => $state->latitude,
                    'longitude' => $state->longitude
                ]);
            $stateBar->advance();
        }

        $stateBar->finish();
        $this->newLine();
        $this->line('stateseeder has now completed');
        $this->newLine(1);
        // states seeder ends here -------------------------------------------

        // >>>>>>>>>>>>>>>>>>> SEEDING COUNTRY <=> TIMEZONES TABLE <<<<<<<<<<<<<<<<<<<<<<<<<<<<
        ini_set('max_execution_time', 1000);
        $data = [];

        $countriesCsvFile = fopen(database_path('csvs/countries.csv'), 'r + b');

        // $countriesCsvFile = fopen(storage_path('newCsvFiles/countries.csv'), 'r+b');

        if ($countriesCsvFile !== FALSE) {
            while (($timezone = fgetcsv($countriesCsvFile, NULL, ',')) !== FALSE) {
                $data[] = $timezone;
            }
            fclose($countriesCsvFile);
        }

        $dataNumRecords = count($data);     // count countries sometimes 195, 252, 250, 276n
        $resultingArray = [];   // this array is going to hold all the string timezones with countryNames as indexes

        $countriesTimezonesProgressBar = $this->output->createProgressBar(428);
        $this->line('   Seeding Timezones');
        $countriesTimezonesProgressBar->start();

        // pulling out timezones as json of each country from 14th colomn of countries.csv,
        // fix all of them via regex, and store in a variable by their 'countries' as indexes
        for ($i = 1; $i < $dataNumRecords; $i++) {
            $resultingArray[$data[$i][1]] = ($this->fixJson($data[$i][14]));
        }

        foreach ($resultingArray as $country => $timezones) {
            $seperatedTimezones = json_decode($timezones, true, 512, JSON_THROW_ON_ERROR);
            $ids = [];

            foreach ($seperatedTimezones as $index => $perTimezone) {
                $timezoneObj = Timezone::updateOrCreate([
                    'name' => $perTimezone['zoneName'],
                    'country' => $country,
                    'gmt_offset' => $perTimezone['gmtOffset'],
                    'gmt_offset_name' => $perTimezone['gmtOffsetName'],
                    'abbreviation' => $perTimezone['abbreviation'],
                    'tz_name' => $perTimezone['tzName']
                ]);

                $ids[] = $timezoneObj->id;

                $countriesTimezonesProgressBar->advance();
            }

            $countriesObj = Country::where('name', $country)->first();
            $countriesObj->timezones()->sync($ids);

        }

        $countriesTimezonesProgressBar->finish();
        $this->newLine();
        $this->newLine(1);
        // >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> COUNTRY TIMEZONE SEED ENDS <<<<<<<<<<<<<<<<<<<<<<<<<<<<<

        return 0;
    }

    private function fixJson(string $str): string {
        return preg_replace(
            '/(?<=(\{|\,))(\w+)(?=\:)/',
            '"$2"',
            str_replace("'", '"', $str)
        );
    }

}
