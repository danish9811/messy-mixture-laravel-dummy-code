<?php

namespace App\Console\Commands;

use App\Models\Currency;
use App\Models\CurrencyRate;
use Artisan;
use Illuminate\Console\Command;
use OTIFSolutions\CurlHandler\Curl;
use OTIFSolutions\Laravel\Settings\Models\Setting;

class FetchCurrencyRates extends Command {

    protected $signature = 'rates:get';
    protected $description = 'Hits the currency layer api, fetch the exchange rates';

    public function handle() {

        // if you have set these two keys, you don't have to write this line again
//         Setting::set('crlKey', 'e568ea241bcd6eb1bc61bc9894943f19');
//         Setting::set('daysRates', 5);

        $accessKey = Setting::get('crlKey');
        $ratesSaveDays = Setting::get('daysRates');   // data of how many days we want to store

        if (!isset($accessKey, $ratesSaveDays)) {
            $this->warn('Either accessy key \'crlKey\' or rates save days \'daysRates\' is not set. Check it how README.md');
            return;
        }

        if (!(is_numeric($ratesSaveDays) && is_numeric(abs($ratesSaveDays)))) {
            $this->warn('daysRates key should be a positive integer');
            return;
        }

        if (Currency::all()->count() === 0) {
            $this->warn('Currency Table is blank | Cannot run the command ');
            $this->line('Populating the tables first');
            Artisan::call('fill:tables');
            $this->info('Re-run the command');
            return;
        }

        $response = Curl::Make()
            ->GET
            ->url('http://api.currencylayer.com/live')
            ->params([
                'access_key' => $accessKey
            ])
            ->execute();

        if (!$response['success']) {
            $this->warn($response['error']['info']);
            return;
        }

        // putting values to the currencies table
        foreach ($response['quotes'] as $i => $value) {
            Currency::where('currency', substr($i, 3, 6))
                ->update([
                    'latest_rate' => $value
                ]);
        }

        // creating the progress bar on exchange_rates table
        $bar = $this->output->createProgressBar(count($response['quotes']));
        $bar->start();
        $this->newLine();

        $source = $response['source'];
        $sourcObj = Currency::firstWhere('currency', $source);

        foreach ($response['quotes'] as $i => $value) {

            $changedTo = substr($i, 3, 6);
            $currencyObj = Currency::firstWhere('currency', $changedTo);

            // what these variables return
            // dd($i, $value, $changedTo, $currencyObj->id);
            // ^ "USDAED"
            // ^ 3.672895
            // ^ "AED"
            // ^ 231

            Currency::where('currency', $changedTo)->update([
                'latest_rate' => $value
            ]);

            // first update or create in currencies table latest_rate

            if ($currencyObj) {
                CurrencyRate::create([
                    'currency_id' => $currencyObj->id,
                    'source_crr' => $source,
                    'converted_crr' => $changedTo,
                    'exchange_rate' => $value,
                    'source_currency_id' => $sourcObj->id
                ]);
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info('Exchange rates synced successfully');

    }
}
