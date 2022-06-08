<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {
    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule) {
        // $schedule->command('inspire')->hourly();
        // $schedule->command('command-goes-here')->daily()->at('08:00');
        // $schedule->command('rates:get')->daily()->at('08:00');
        $schedule->command('rates:delete')->daily()->at('08:00');
    }


    // NOTE :: PROGRAMMERS
    // these commands are scheduled only in outside of the package, but we have want to run them
    // in our currencylayerpackage, we simple put the sheduled command in currencyLayerServiceProver boot() method

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands() {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
