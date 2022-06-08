<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\State;
use Illuminate\Database\Seeder;
use JsonMachine\Exception\InvalidArgumentException;
use JsonMachine\Items;

class CitySeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function run() {
        ini_set('max_execution_time', 1000);
        $cities = Items::fromFile(__DIR__ . './../jsons/cities.json');
        $data = collect();

        // we are making the $data as collection and passing array of data to it, isn't it weird and bad approach
        foreach ($cities as $key => $city) {
            City::updateOrCreate([
                'name' => $city->name,
                'state_code' => $city->state_code
            ], [
                'name' => $city->name,
                'state_code' => $city->state_code,
                'latitude' => $city->latitude,
                'longitude' => $city->longitude,
                'wiki_data_id' => $city->wikiDataId,
                'state_id' => State::where('state_code', $city->state_code)->first()['id']
            ]);
        }

//        foreach ($data->chunk(5000) as $chunk) {
//            City::upsert($chunk->toArray(), [
//                'name', 'state_id'
//            ], ['name', 'state_code', 'latitude', 'longitude', 'wiki_data_id', 'state_id']);
//        }

    }

}
