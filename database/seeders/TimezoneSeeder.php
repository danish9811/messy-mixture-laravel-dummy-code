<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Timezone;
use Illuminate\Database\Seeder;

class TimezoneSeeder extends Seeder {

    public function run() {

        // dd(explode(',', '{},{}'));  // 1 => {}, 2 => {}

        ini_set('max_execution_time', 1000);
        $data = [];

        // reading countries csv
        if (($open = fopen(public_path('csvJsonFiles/countries.csv'), 'r + b')) !== FALSE) {
            while (($timezone = fgetcsv($open, NULL, ',')) !== FALSE) {
                $data[] = $timezone;
            }
            fclose($open);
        }

        // dd($data);  // outputting all countries with long timezones
        // remove slashes and brackets only from 14 colomn of timezezones, not all
//        $data = $this->replaceChars($data, ['[', ']']); // removed manually
//        $data = $this->replaceChars($data, (array)'\/', ' - '); // removed manually

        $dataNumRecords = count($data);     // count countries sometimes 195, 252, 250, 276n
        $resultingArray = [];   // this array is holding all the string timezones with countryNames as indexes
        $subArray = [];
        $countryNames = []; // this array is holding all the countryNames



        // put all the countryNames into the indexes and all the timezones that country has are
        // the long string, fix all the jsons occuring at 14th colomn of the countries.csv
        for ($i = 1; $i < $dataNumRecords; $i++) {
            $resultingArray[$data[$i][1]] = ($this->fixJson($data[$i][14]));
            $countryNames[] = $data[$i][1]; // filling all the countryNames
        }

        // we have not exploded the strings yet, so there are no changes of error like NULL timezones
        // in this array, the index is the countryName and its values are timezones
        // so we have to put the countryName at its position alongwith the timezones


         // dd(json_decode($resultingArray['Russia']));

        $count =0;  // 250 countries are coming in this array, so only first sub-array was being executed
        foreach($resultingArray as $country => $timezones) {
            $seperatedTimezones = json_decode($timezones, true, 512, JSON_THROW_ON_ERROR);
//             dd($seperatedTimezones, $country);
            $ids = [];
            foreach($seperatedTimezones as $index => $perTimezone) {
//                dd($seperatedTimezones);
                $count++;
//                dd($index, $perTimezone);
                $timezoneObj = Timezone::create([
                   'name' => $perTimezone['zoneName'],
                   'country' => $country,
                   'gmt_offset' => $perTimezone['gmtOffset'],
                   'gmt_offset_name' => $perTimezone['gmtOffsetName'],
                   'abbreviation' => $perTimezone['abbreviation'],
                   'tz_name' => $perTimezone['tzName']
                ]);

                $ids[] = $timezoneObj->id;

            }
            $cc_country = Country::where('name', $country)->first();
            // dd($cc_country->id);
            $cc_country->timezones()->sync($ids);
//            unset($seperatedTimezones);

        }

        dd($count);

















        dd();
        dd($countryNames);
        dd(json_decode($resultingArray['Russia']));    // show all the timezones with countries they are indexed with, using regex

        $jsonData = [];
        foreach ($resultingArray as $singleString) {
            if (is_null(json_decode($singleString))) {
                $singleString .= '}';   // this because we have done explode on the behalf of
            }
            $jsonData[] = json_decode($singleString);
        }

        foreach ($resultingArray as $key => $jsonData) {

            if (str_contains($resultingArray[$key], '},')) {  // this line means that certain country does have multiple timezones
                $subArray[] = $this->fixJsonString(explode('},', $resultingArray[$key]));
                unset($resultingArray[$key]);
            } else {
                $subArray[] = $jsonData;
            }

            $countryObj = Country::where('name', $key)->first();

            foreach ($subArray as $timezone) {
                $ids = [];
                if (is_string($timezone)) {     // if string, then  there is single timezone
                    $ids[] = $this->insertTimezone($timezone);
                } else {    // if array, then there are muliple timezones
                    $timezones = $this->fixJsonString($timezone);
                    foreach ($timezones as $singleTimezone) {
                        $ids[] = $this->insertTimezone($singleTimezone);
                    }
                }
            }
            $countryObj->timezones()->sync(($ids ?? []));
        }
    }





    private function fillTimezones(array $timezones, string $countryName)  {
        $jsons = json_decode($timezones);
        Timezone::create([
           'name' => $jsons['name']
        ]);
    }



    public function insertTimezone(string $timezone) {
        $timezone = json_decode($timezone);
        $timezoneObj = Timezone::updateOrCreate(['name' => $timezone->zoneName], [
            'name' => $timezone->zoneName,
            'gmt_offset' => $timezone->gmtOffset,
            'gmt_offset_name' => $timezone->gmtOffsetName,
            'abbreviation' => $timezone->abbreviation,
            'tz_name' => $timezone->tzName,
        ]);

        return $timezoneObj['id'];
    }


    // this method is no longer used

//    public function replaceChars($hayStack, array $charsArray, $character = ''): array {
//        $tempArray = [];
//        foreach ($hayStack as $item) {
//            $tempArray[] = str_replace($charsArray, $character, $item);
//        }
//        return $tempArray;
//    }

    private function fixJson(string $str): string {
        return preg_replace(
            '/(?<=(\{|\,))(\w+)(?=\:)/',
            '"$2"',
            str_replace("'", '"', $str)
        );
    }

    public function fixJsonString(array $stringsArray): array {
        foreach ($stringsArray as $index => $string) {
            if (!(substr($string, -1) === '}')) {
                $stringsArray[$index] = $string . '}';
            }
        }
        return $stringsArray;
    }

}
