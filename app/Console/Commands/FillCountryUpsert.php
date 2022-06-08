<?php

namespace App\Console\Commands;

use App\Models\Country;
use Illuminate\Console\Command;

class FillCountryUpsert extends Command {

    protected $signature = 'fill:countries';
    protected $description = 'Command description';

    public function handle() {


        $countriesArr = [];
        if (($open = fopen(storage_path('newCsvFiles/countries.csv'), "r + b")) !== FALSE) {
            while (($singleRecord = fgetcsv($open, NULL, ",")) !== FALSE) {
                $countriesArr[] = $singleRecord;
            }
            fclose($open);
        }

        array_shift($countriesArr);

        $insertArray = [];

        foreach ($countriesArr as $value) {
            $insertArray[] = [
                'id' => $value[0],
                'name' => $value[1],
                'iso3' => $value[2],
                'iso2' => $value[3],
                'numeric_code' => $value[4],
                'phone_code' => $value[5],
                'capital' => $value[6],
                'tld' => $value[10],
                'native' => $value[11],
                'region' => $value[12],
                'subregion' => $value[13],
                'latitude' => $value[15],
                'longitude' => $value[16],
                'flag' => strtolower($value[3]) . '.png'
            ];

        }

        Country::upsert($insertArray, ['id']);

        return 0;
    }
}
