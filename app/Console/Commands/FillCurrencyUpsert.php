<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\Currency;
use Illuminate\Console\Command;

class FillCurrencyUpsert extends Command {

    protected $signature = 'fill:currency';
    protected $description = 'Command description';

    public function handle() {

        $currenciesArr = [];
        if (($open = fopen(storage_path('newCsvFiles/countries.csv'), "r + b")) !== FALSE) {
            while (($singleRecord = fgetcsv($open, NULL, ",")) !== FALSE) {
                $currenciesArr[] = $singleRecord;
            }
            fclose($open);
        }

        array_shift($currenciesArr);

        $insertArray = [];
        foreach ($currenciesArr as $value) {
            $insertArray[] = [
                'currency' => $value[7],
                'name' => $value[8],
                'symbol' => $value[9],
                'country_id' => Country::where('iso3', $value[2])->first()['id'],
            ];
        }

        Currency::upsert($insertArray, ['country_id']);

        return 0;
    }
}
