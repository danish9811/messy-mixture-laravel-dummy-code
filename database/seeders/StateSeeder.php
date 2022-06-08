<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\State;
use Illuminate\Database\Seeder;
use JsonMachine\Items;

class StateSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     * @throws \JsonMachine\Exception\InvalidArgumentException
     */
    public function run() {
        $states = Items::fromFile(__DIR__ . './../jsons/states.json');
        foreach ($states as $state) {

//            State::updateOrCreate([
//                'name' => $state->name,
//                'state_code' => $state->state_code
//            ],
//                [
//                    'name' => $state->name,
//                    'state_code' => $state->state_code,
//                    'country_id' => Country::where('iso2', $state->country_code)->first()['id'],
//                    'latitude' => $state->latitude,
//                    'longitude' => $state->longitude
//                ]);

            State::create([
               'name' => $state->name,
               'state_code' => $state->state_code,
               'country_id' => Country::where('iso2', $state->country_code)->first()['id'],
               'latitude' => $state->latitude,
               'longitude' => $state->longitude
            ]);




        }

//        collect($data)
//            ->map(function (array $row) {
//                return State::withOnly($row, ['email', 'name', 'address']);
//            })
//            ->chunk(1000)
//            ->each(function (Collection $chunk) {
//                Contact::upsert($chunk, 'email');
//            });

//        State::upsert($data, [
//            'name', 'state_code', 'country_id', 'latitude', 'longitude'
//        ], ['latitude', 'longitude']);

    }

}
