<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\Currency;
use Illuminate\Console\Command;

class CurrencySeederProgress extends Command {

    protected $signature = 'seed:currencies';
    protected $description = 'Command description';

    public function handle() {

        $currenciesData = [];
        if (($open = fopen(storage_path('newCsvFiles/countries.csv'), "r + b")) !== FALSE) {
            while (($singleRecord = fgetcsv($open, NULL, ",")) !== FALSE) {
                $currenciesData[] = $singleRecord;
            }
            fclose($open);
        }

        $countCurrencies = count($currenciesData);
        $currencyBar = $this->output->createProgressBar($countCurrencies - 1);

        $this->line('currencies seeding started');
        $this->newLine();
        $currencyBar->start();

//        for ($i = 1; $i < $countCurrencies; $i++) {
//            $insertArray[$i] = [
//                'currency' => $currenciesData[$i][7], // AFN
//                'name' => $currenciesData[$i][8],     //  afghan afghani
//                'symbol' => $currenciesData[$i][9],   // ؋
//                'country_id' => Country::where('iso2', $currenciesData[$i][3])->first()['id'],
//            ];
//            $currencyBar->advance();
//        }

        array_shift($currenciesData);
        $insertArray = [];
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
        $this->line('ended currencies seeder here');
        $this->newLine(1);

        return 0;
    }
}
