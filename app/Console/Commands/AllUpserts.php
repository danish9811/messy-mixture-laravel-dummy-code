<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Models\Country;
use App\Models\Currency;
use App\Models\State;
use App\Models\Timezone;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AllUpserts extends Command {

    protected $signature = 'fill:tables';
    protected $description = 'Initiate filling datbase tables by reading from csvs';

    public function handle() {

        $this->warn('Please don\'t close the terminal while tables are being populated');
        $this->newLine();


        $fillingStartsTime = Carbon::now();

        $countriesCsvArr = [];
        // reading from countries.csv
        ($open = fopen(storage_path('newCsvFiles/countries.csv'), "r + b"));
        if ($open !== FALSE) {
            while (($singleRecord = fgetcsv($open, NULL, ",")) !== FALSE) {
                $countriesCsvArr[] = $singleRecord;
            }
            fclose($open);
        }

        array_shift($countriesCsvArr);

        $totalRecordsCountriesCsv = count($countriesCsvArr);    // after shifting

        // progress bar created
        $countryBar = $this->output->createProgressBar($totalRecordsCountriesCsv);
        $this->line('  Populating Countries table');
        $countryBar->start();

        $insertArray = [];

        foreach ($countriesCsvArr as $value) {
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
            $countryBar->advance();

        }

        Country::upsert($insertArray, ['id']);
        $countryBar->finish();
        $this->newLine();

        // >>>>>>>>>>>>>>>>> populating countries ended here <<<<<<<<<<<<<<<<<<<<<<<<<<

        // reading againt the csv, in-efficient
//        $currenciesArr = [];
//        if (($open = fopen(storage_path('newCsvFiles/countries.csv'), "r + b")) !== FALSE) {
//            while (($singleRecord = fgetcsv($open, NULL, ",")) !== FALSE) {
//                $currenciesArr[] = $singleRecord;
//            }
//            fclose($open);
//        }

        // array_shift($currenciesArr);
        $countryBar = $this->output->createProgressBar($totalRecordsCountriesCsv);
        $this->line('  Populating Currencies table');
        $countryBar->start();

        $insertArray = [];
        foreach ($countriesCsvArr as $value) {
            $insertArray[] = [
                'currency' => $value[7],
                'name' => $value[8],
                'symbol' => $value[9],
                'country_id' => Country::where('iso3', $value[2])->first()['id'],
            ];
            $countryBar->advance();
        }

        $countryBar->finish();
        $this->newLine();

        Currency::upsert($insertArray, ['country_id']);

        // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        // >>>>>>>>>>>>>>>>>> populating currencies table ended here <<<<<<<<<<<<<<<<<<<<
        // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

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
        $this->line('  Populating Timezones table');
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
        $this->newLine(1);

        // +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        // >>>>>>>>>>>>>>> populating timezones + pivot with countries table ended here <<<<<<
        // +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

        $statesArr = [];
        if (($open = fopen(storage_path('newCsvFiles/states.csv'), "r + b")) !== FALSE) {
            while (($singleRecord = fgetcsv($open, NULL, ",")) !== FALSE) {
                $statesArr[] = $singleRecord;
            }
            fclose($open);
        }

//        dd($statesArr[0]);

        array_shift($statesArr);

        $statesBar = $this->output->createProgressBar(count($statesArr));
        $this->line('  Populating States table');
        $statesBar->start();


        $insertArray = [];
        foreach ($statesArr as $value) {
//            dd($value);
            $insertArray[] = [
                'id' => $value[0],
                'name' => $value[1],
                'state_code' => $value[5],
                'country_id' => $value[2],  // relation is already made in csvs
                'latitude' => $value[7],
                'longitude' => $value[8]
            ];

            $statesBar->advance();
//            dd($insertArray);
        }

        State::upsert($insertArray, ['id']);    // the id of state is unique, not the countryId
        $statesBar->finish();
        $this->newLine(1);


        // +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        // >>>>>>>>>>>>>>>>>>>>>>> populating states table ended here <<<<<<<<<<<<<<<<<<<<<<<<
        // +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++


        ini_set('memory_limit', '256M');
        $totalCities = 148249;
        $filename = storage_path('newCsvFiles/cities.csv');

        $citiesBar = $this->output->createProgressBar($totalCities);
        $this->line('  Populating Cities table');
        $citiesBar->start();

        $rows = $this->rowsCountCsv($filename);
        $itemsPerRun = 50000;
        for ($i = 0; $i <= $rows; $i += $itemsPerRun + 1) {
            $cities = [];
            $chunk = $this->csvSlice($filename, $i, $itemsPerRun);
            foreach ($chunk as $item) {

                $cities[] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'latitude' => $item->latitude,
                    'longitude' => $item->longitude,
                    'wiki_data_id' => $item->wikiDataId,
                    'state_id' => $item->state_id
                ];

                $citiesBar->advance();

            }

            foreach (array_chunk($cities, 5000) as $part) {
                City::upsert(
                    $part, ['id']
                );

            }
        }

        $citiesBar->finish();
        $this->newLine();


        $end = Carbon::now();
        $this->newLine();
        $this->info('Data populating took ' . $end->diffInSeconds($fillingStartsTime) . ' seconds');
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

    private function rowsCountCsv($filename) {
        ini_set('auto_detect_line_endings', TRUE);
        $countRows = 0;
        if (($handle = fopen($filename, 'rb')) !== FALSE) {
            while (($row_data = fgetcsv($handle, 2000, ",")) !== FALSE) {
                $countRows++;
            }
            fclose($handle);
            // Exclude the headings.
            $countRows--;
            return $countRows;
        }
    }

    private function csvSlice($filename, $start, $desiredCount) {
        $row = 0;
        $count = 0;
        $rows = array();
        if (($handle = fopen($filename, 'rb')) === FALSE) {
            return FALSE;
        }
        while (($rowData = fgetcsv($handle, 2000, ",")) !== FALSE) {
            // Grab headings.
            if ($row === 0) {
                $headings = $rowData;
                $row++;
                continue;
            }

            // Not there yet.
            if ($row++ < $start) {
                continue;
            }

            $rows[] = (object)array_combine($headings, $rowData);
            $count++;
            if ($count == $desiredCount) {
                return $rows;
            }
        }
        return $rows;
    }
}
