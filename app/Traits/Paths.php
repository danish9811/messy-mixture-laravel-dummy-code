<?php

namespace App\Traits;

trait Paths {

    public string $countriesJson;
    public string $countriesCsv;
    public string $statesJson;
    public string $statesCsv;
    public string $citiesJson;
    public string $citiesCsv;
    public string $timezonesJson;
    public string $timezonesCsv;

    public function __construct() {
        $this->countriesJson = database_path('jsons/countries.json');
    }

    public function getCountriesJsonFile() {
        return $this->countriesJson;
    }

    public function paths() {
        return [
            'countriesJson' => database_path('jsons/countries.json'),
            'countries_json' => database_path('jsons/countries.json')
        ];
    }

}


