<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\Timezone;
use Illuminate\Console\Command;

class CountryTimezoneWithProgressBar extends Command {

    protected $signature = 'seed:country-timezone';
    protected $description = 'Command description';

    public function handle() {

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
