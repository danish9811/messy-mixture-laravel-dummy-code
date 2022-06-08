<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\Currency;
use App\Models\State;
use Illuminate\Console\Command;

class FillStateUpsert extends Command {

    protected $signature = 'fill:states';
    protected $description = 'Command description';

    public function handle() {


        $statesArr = [];
        if (($open = fopen(storage_path('newCsvFiles/states.csv'), "r + b")) !== FALSE) {
            while (($singleRecord = fgetcsv($open, NULL, ",")) !== FALSE) {
                $statesArr[] = $singleRecord;
            }
            fclose($open);
        }

//        dd($statesArr[0]);

        array_shift($statesArr);

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
//            dd($insertArray);
        }

        State::upsert($insertArray, ['id']);    // the id of state is unique, not the countryId


        return 0;
    }
}
