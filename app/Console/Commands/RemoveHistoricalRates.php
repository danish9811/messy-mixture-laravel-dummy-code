<?php

namespace App\Console\Commands;

use App\Models\CurrencyRate;
use Carbon\Carbon;
use Illuminate\Console\Command;
use OTIFSolutions\Laravel\Settings\Models\Setting;

class RemoveHistoricalRates extends Command {

    protected $signature = 'rates:delete';
    protected $description = 'Remove all the exchange rates';

    public function handle() {

        $accessKey = Setting::get('crkey');
        $ratesSaveDays = Setting::get('days_rates');   // data of how many days we want to store

        if (!isset($accessKey, $ratesSaveDays)) {
            $this->warn('Either accessy key \'crkey\' or rates save days \'days_rates\' is not set. Check it how README.md');
            return;
        }

        if (!(is_numeric($ratesSaveDays) && is_numeric(abs($ratesSaveDays)))) {
            $this->warn('days_rates key should be a positive integer');
            return;
        }


        if (CurrencyRate::exists()) {
            CurrencyRate::whereDate('created_at', '<=', Carbon::now()->subDays($ratesSaveDays))->delete();
            $this->info('Exchange rates synced successfully');
            $this->newLine();
        }

    }
}
