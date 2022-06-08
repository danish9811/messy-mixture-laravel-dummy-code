<?php

namespace App\Http\Controllers;

use App\Models\{Country, Currency};
use App\Traits\Utils;

class WorkingMethods extends Controller {
    use Utils;

    public function index() {   // index
        return __DIR__ . ' called method \n';
    }

    public function fillCurrency() {    // fill-currency
        // $data[0]
        // 0 => "id"
        // 1 => "name"
        // 2 => "iso3"
        // 3 => "iso2"
        // 4 => "numeric_code"
        // 5 => "phone_code"
        // 6 => "capital"
        // 7 => "currency"
        // 8 => "currency_name"
        // 9 => "currency_symbol"
        // 10 => "tld"
        // 11 => "native"
        // 12 => "region"
        // 13 => "subregion"
        // 14 => "timezones"
        // 15 => "latitude"
        // 16 => "longitude"
        // 17 => "emoji"
        // 18 => "emojiU"

        $data = [];
        if (($open = fopen(__DIR__ . '/countries.csv', "r + b")) !== FALSE) {
            while (($student = fgetcsv($open, NULL, ",")) !== FALSE) {
                $data[] = $student;
            }
            fclose($open);
        }

        $insertArray = [];
        $totalRecords = count($data);
        for ($i = 1; $i < $totalRecords; $i++) {
            $insertArray[$i] = [
                'currency' => $data[$i][7], // AFN
                'name' => $data[$i][8],     //  afghan afghani
                'symbol' => $data[$i][9],   // ؋
                'country_id' => Country::where('iso2', $data[$i][3])->first()['id'],
            ];
        }

        Currency::upsert($insertArray, [
            'country_id'
        ]);

    }

    public function readStatesFromStatesCsvAndFillTable() {
        $statesArr = [];
        if (($open = fopen(storage_path('newCsvFiles/states.csv'), "r + b")) !== FALSE) {
            while (($singleRecord = fgetcsv($open, NULL, ",")) !== FALSE) {
                $statesArr[] = $singleRecord;
            }
            fclose($open);
        }

        dd(count($statesArr));

    }

    public function readCitiesFromCitiesCsvAndFillTable() {
        $citiesArr = [];
        $citiesCoutner = 0;
        if (($open = fopen(storage_path('newCsvFiles/cities.csv'), "r + b")) !== FALSE) {
            while (($singleRecord = fgetcsv($open, NULL, ",")) !== FALSE) {
                $citiesArr[] = $singleRecord;
                $citiesCoutner++;
            }
            fclose($open);
        }

        dd($citiesCoutner);

    }

    public function readTimezonesFromCountriesCsv() {

    }

    public function readCountriesFromCountriesCsvAndFill() {

        $countriesArr = [];
        if (($open = fopen(storage_path('newCsvFiles/countries.csv'), "r + b")) !== FALSE) {
            while (($singleRecord = fgetcsv($open, NULL, ",")) !== FALSE) {
                $countriesArr[] = $singleRecord;
            }
            fclose($open);
        }

        for ($i = 1; $i <= 250; $i++) {
            Country::updateOrCreate([
                'name' => $countriesArr[$i][1], // Afghanistan
                'iso3' => $countriesArr[$i][2], // AFG
                'iso2' => $countriesArr[$i][3], // af
                'numeric_code' => $countriesArr[$i][4], // 004
                'phone_code' => $countriesArr[$i][5], // +93
                'capital' => $countriesArr[$i][6],   // Kabul
                'tld' => $countriesArr[$i][10],   //  .af
                'native' => $countriesArr[$i][11], // افغانستان
                'region' => $countriesArr[$i][12], // Asia
                'subregion' => $countriesArr[$i][13],   // southern Asia
                'latitude' => $countriesArr[$i][15],
                'longitude' => $countriesArr[$i][16],
                'flag' => strtolower($countriesArr[$i][3])
            ]);
        }

    }


    public function words() {
        $countriesArr = [];
        if (($open = fopen(storage_path('newCsvFiles/countries.csv'), "r + b")) !== FALSE) {
            while (($singleRecord = fgetcsv($open, NULL, ",")) !== FALSE) {
                $countriesArr[] = $singleRecord;
            }
            fclose($open);
        }

        $singleTimezone = $countriesArr[9][14];

        dd(explode(',', $singleTimezone));

        $word = 'zoneName';
        $newWord = '"' . $word . '"';
        echo $newWord;

    }

    public function showTimezonesFromCountriesCsv() {
        $timezonesArr = [];
        if (($open = fopen(storage_path('newCsvFiles/countries.csv'), "r + b")) !== FALSE) {
            while (($singleRecord = fgetcsv($open, NULL, ",")) !== FALSE) {
                $timezonesArr[] = $singleRecord;
            }
            fclose($open);
        }
    }

//        $bigString = str_replace('\"\"', '', $bigString);
//        echo $bigString;
//        $this->printFormattedData($jsonArray);  // clear for formatted php array
//        echo json_encode($jsonArray);
//        print_r(json_decode(file_get_contents(database_path('json'))))
//        dd(json_decode(file_get_contents(database_path('jsons/timezones.json'))));
//        print_r(json_decode(database_path('jsons/timezones.json'), false));




    public function fillStatesWithUpsertMethod() {
        $timezonesJson = file_get_contents(database_path('jsons/timezones.json'));
        $timezonesJson = json_decode($timezonesJson);
        echo '<pre>';
        foreach($timezonesJson as $item) {
            print_r($item->zoneName);
            echo '<br>';
        }
    }
















}
