<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class CallPackageSeeders extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:seeders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'run all the package seeders to populate all tables';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {

        $this->info('Please wait for few seconds while seeding completes');
        Artisan::call('db:seed --class=\\OTIFSolutions\\CurrencyLayer\\Database\\Seeders\\CountrySeeder');
        Artisan::call('db:seed --class=\\OTIFSolutions\\CurrencyLayer\\Database\\Seeders\\StateSeeder');
        Artisan::call('db:seed --class=\\OTIFSolutions\\CurrencyLayer\\Database\\Seeders\\CurrencySeeder');
        Artisan::call('db:seed --class=\\OTIFSolutions\\CurrencyLayer\\Database\\Seeders\\TimezoneSeeder');
        Artisan::call('db:seed --class=\\OTIFSolutions\\CurrencyLayer\\Database\\Seeders\\CitySeeder');

        $this->info('seeding successful');
        $this->newLine();

        return 0;
    }
}
