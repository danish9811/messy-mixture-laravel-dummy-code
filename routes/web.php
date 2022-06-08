<?php

use App\Http\Controllers\{PractController, ProgramsController, TestController, WorkingMethods};

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');
//Route::view('js', 'learningJS.newFile');
Route::view('learning-js', 'learningJS.newFile');


Route::get('test-method', [TestController::class, 'testMethod']);
Route::get('states-get', [WorkingMethods::class, 'readStatesFromStatesCsvAndFillTable']);
Route::get('cities-get', [WorkingMethods::class, 'readCitiesFromCitiesCsvAndFillTable']);
Route::get('countries-fill', [WorkingMethods::class, 'readCountriesFromCountriesCsvAndFill']);
Route::get('timezones-get', [WorkingMethods::class, 'showTimezonesFromCountriesCsv']);
Route::get('words', [WorkingMethods::class, 'words']);
Route::get('states-upsert', [WorkingMethods::class, 'fillStatesWithUpsertMethod']);



// -----------------------------------------------------------------------------
Route::get('many', [PractController::class, 'index']);
Route::get('qiro-many-to-many', [PractController::class, 'qiroLabManyToMany']);
Route::get('qiro-one-to-one', [PractController::class, 'qiroOneToOne']);
Route::get('qiro-one-to-many', [PractController::class, 'qiroOneToMany']);

Route::get('check-path', [PractController::class, 'checkPath']);

Route::get('timezone', [TestController::class, 'readTimezoneFromCountiesCsv']); // dummy
Route::get('fill-zones', [TestController::class, 'timezone']);  // with sample model, under construction method
Route::get('fill-timezone-json-machine', [TestController::class, 'fillTimezone']);  // the actuall method i'm looking for
Route::get('check', [TestController::class, 'w3schoolConvertMethodCSV']);   // reading csv file, faltu code
Route::get('third', [TestController::class, 'thirdMethod']);    // reading csv file and showing it, NICE CODE
Route::get('json', [TestController::class, 'readJsonFile']);    // read sample.json file and prints it, NICE CODE
Route::get('fill-table-json',[TestController::class, 'fillTableJson']);     // reads data from sample.json and fillinto the table via Model
Route::get('fill-table-csv',[TestController::class, 'fillTableC']); // unknow method
Route::get('fill-cities-table', [TestController::class, 'fillCitiesTable']);  // good practice method, understand upsert
Route::get('show-currencies-and-fill', [TestController::class, 'currencyNameFromCountryTable']); // show currencyies from country csv and insert into table
Route::get('timezone-seeder', [TestController::class, 'timezoneSeeder']);   // method being used in seeders,
Route::get('exported-timezones-insert', [TestController::class, 'exportedTimezonesInsertion']);




// laravel 9 way of grouping controllers
Route::controller(TestController::class)->group(static function () {
//    Route::get('json', 'readJsonFile');
//    Route::get('fillmodel', 'fillTableJson');
//    Route::get('filltable', 'fillTableCsv');
    // Route::get('fillcity', 'fillCitiesTable');
    // Route::get('currency', 'currencyNameFromCountryTable');
    Route::get('country', 'showCountries');
    Route::get('fill-countries', 'fillCountries');
    Route::get('fill-flags-countries', 'fillFlagsInCountriesTable');
    Route::get('carbon', 'useCarbon');
    Route::get('json-machine', 'jsonMachine');
    Route::get('insert-country', 'insertCountry');
    Route::get('state-filler', 'stateFiller');
    // Route::get('timezone-seeder', 'timezoneSeeder');
    Route::get('city-filler', 'cityFiller');
    Route::get('fill-currency', 'fillCurrency');


    // routes for API's methods
    Route::get('get-currency-layer-results', 'getCurrencyLayerApiResultAndFillTable');
    Route::get('test', 'indexTestMethod');
    Route::get('currency-layer-curl', 'getCurrencyLayerResponseViaOtifCurl');
    Route::get('access-key', 'accessKey');

    // view related routes
    Route::get('randoms', 'playWithRandomNumbers');
});


Route::controller(WorkingMethods::class)->group(static function () {
    Route::get('index', 'index');
    Route::get('fill-currency', 'fillCurrency');
});


Route::controller(ProgramsController::class)->group(static function () {
    Route::get('one-to-one', 'oneToToOne');
    Route::get('one-to-many', 'oneToMany');
    Route::get('many-to-many', 'manyToMany');
});



