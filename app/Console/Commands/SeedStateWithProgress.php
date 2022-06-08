<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\State;
use Illuminate\Console\Command;

class SeedStateWithProgress extends Command {

    protected $signature = 'seed:states';
    protected $description = 'Command description';

    public function handle() {

//        $states = Items::fromFile(database_path('jsons/states.json'));

        $states = json_decode(file_get_contents(database_path('jsons/states.json')));

        $stateBar = $this->output->createProgressBar(4964);
        $this->line('starting state seeding');
        $this->newLine();
        $stateBar->start();

        foreach ($states as $state) {
            State::updateOrCreate([

            ], [
                'name' => $state->name,
                'state_code' => $state->state_code,
                'country_id' => Country::where('iso2', $state->country_code)->first()['id'],
                'latitude' => $state->latitude,
                'longitude' => $state->longitude
            ]);
            $stateBar->advance();
        }

        $stateBar->finish();
        $this->newLine();
        $this->line('Seeding states table completed');
        $this->newLine(1);

        return 0;

    }
}
