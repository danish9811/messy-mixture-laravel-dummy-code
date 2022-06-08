<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $data = [];
        if (($open = fopen(__DIR__ . './../csvs/countries.csv', "r + b")) !== FALSE) {
            while (($student = fgetcsv($open, NULL, ",")) !== FALSE) {
                $data[] = $student;
            }
            fclose($open);
        }

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
}
