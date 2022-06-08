<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder {

    public function run() {




        // read countries.csv
        $countriesArr = [];
        if (($open = fopen(storage_path('newCsvFiles/countries.csv'), "r + b")) !== FALSE) {
            while (($singleRecord = fgetcsv($open, NULL, ",")) !== FALSE) {
                $countriesArr[] = $singleRecord;
            }
            fclose($open);
        }

        array_shift($countriesArr);

        $insertArray = [];
//        dd($countriesArr[0]);
        foreach($countriesArr as $value) {
            $insertArray[] = [
                'id' => $value[0],
                'name' => $value[1],
                'iso3' => $value[2],
                'iso2' => $value[3],
                'numeric_code' => $value[4],
                'phone_code' => $value['5'],
                'capital' => $value[6],
                'tld' => $value[10],
                'native' => $value[11],
                'region' => $value[12],
                'subregion' => $value[13],
                'latitude' => $value[15],
                'longitude' => $value[16],
                'flag' => strtolower($value[3]) . '.png'
            ];

//            dd($insertArray);
        }


        Country::upsert($insertArray, ['id']);

















//        $countries = json_decode(file_get_contents(database_path('jsons/countries.json')), true, 512, JSON_THROW_ON_ERROR);
//
//        foreach ($countries as $country) {
//            Country::updateOrCreate([
//                'name' => $country['name'], // Afghanistan
//                'iso3' => $country['iso3'], // AFG
//                'iso2' => $country['iso2'], // af
//                'numeric_code' => $country['numeric_code'], // 004
//                'phone_code' => $country['phone_code'], // +93
//                'capital' => $country['capital'],   // Kabul
//                'tld' => $country['tld'],   //  .af
//                'native' => $country['native'], // افغانستان
//                'region' => $country['region'], // Asia
//                'subregion' => $country['subregion'],   // southern Asia
//                'latitude' => $country['latitude'],
//                'longitude' => $country['longitude'],
//                'flag' => $country['flag']
//            ]);
//        }
    }

}
