<?php

namespace App\Http\Controllers;

use App\Models\{City, Country, Currency, CurrencyRate, OneTimezone, SampleModel, State, Timezone};
use App\Traits\Utils;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request;
use JsonMachine\Items;
use JsonMachine\JsonDecoder\ExtJsonDecoder;
use OTIFSolutions\Laravel\Settings\Models\Setting;

class TestController extends Controller {
    use Utils;

    public function __construct() {
        set_time_limit(100);
    }

    public function testMethod(Request $request) {  // endPoint : test-method
        // get all the timezone zoneNames as array
        // $this->printFormattedData(Timezone::get('name')->toArray());
        // $this->printFormattedData($timezonesArray);

        // dd(Timezone::select('tz_name')->distinct()->get());

        $arr = Timezone::all(['name']);
        $count = count($arr);
        for($i = 0; $i < $count; $i++ ) {
            echo $i . ' => ' .  $arr[$i]['name'] . '<br>';
        }


    }



    // this method is mixed, and using another approeach, first fill all timezones by reading countries.csv
    // then import that file and use it here
    public function fillTimezone() {    // endpoint : fill-timezone-json-machine
        // $timezones = Items::fromFile(database_path('jsons/countries-org.json'));
        $timezones = Items::fromFile(database_path('jsons/countries.json'));

        $data = [];
        foreach ($timezones as $timezone) {
            $data[] = [
                'name' => $timezone->name,
                'gmt_offset' => $timezone->country_code,
                'gmt_offset_name' => $timezone->state_code,
                'abbreviation' => Country::where('iso2', $timezone->country_code)->first()['id'],
                'tz_name' => $timezone->tzName
            ];
        }

        State::upsert($data, [
            'name', 'symbol'
        ], ['name', 'country_code', 'state_code', 'country_id', 'latitude', 'longitude']);

        // foreach ($jsonData as $singleRecord) {
        //     $singleRecord = (array)($singleRecord);     // converting all StdClass objects to array
        //     Timezone::create([
        //         'zone_name' => $singleRecord['zoneName'],
        //         'gmt_offset' => $singleRecord['gmtOffset'],
        //         'gmt_offset_name' => $singleRecord['gmtOffsetName'],
        //         'abbreviation' => $singleRecord['abbreviation'],
        //         'tz_name' => $singleRecord['tzName'],
        //     ]);
        // }
    }

    // working method fill-currency
    public function fillCurrency() {    // fill-currency

        // $data[0]
        // 0 => "id"
        // 1 => "name"
        // 2 => "iso3"
        // 3 => "iso2"
        // 4 => "numeric_code"
        // 5 => "phone_code"
        // 6 => "capital"
        // 7 => "currency"
        // 8 => "currency_name"
        // 9 => "currency_symbol"
        // 10 => "tld"
        // 11 => "native"
        // 12 => "region"
        // 13 => "subregion"
        // 14 => "timezones"
        // 15 => "latitude"
        // 16 => "longitude"
        // 17 => "emoji"
        // 18 => "emojiU"

        $data = [];
        if (($open = fopen(__DIR__ . '/countries.csv', "r + b")) !== FALSE) {
            while (($student = fgetcsv($open, NULL, ",")) !== FALSE) {
                $data[] = $student;
            }
            fclose($open);
        }

        // dd($data[3][3]);
        // dd(Country::where('iso2', '=', $data[3][3]));
        // dd(Country::get()[7]);  // 8th record, Anguilla
        // dd($data[0]);
        // dd($data[3]);    // complete 3rd record
        // dd($data[3][4]); // single string
        // dd(\App\Models\Country::all('iso2'));
        // dd(\App\Models\Country::where('id', 2)->get());
        // $data = array_slice($data, 1);  // 0th record contains colomnNames
        // dd($data[0], count($data));   // if we say 0, it means the first currency afghan afghani

        // pulling out currency details from countries.csv
        // foreach($data as $key => $value) {
        // // dd(Country::where('iso2', $data[$key][3])->first()['id']);
        //     $insertArray[$key] = [
        //         'currency' => $data[$key][7], // AFN
        //         'name' => $data[$key][8],     //  afghan afghani
        //         'symbol' => $data[$key][9],   // ؋
        //         'country_id' => Country::where('iso2', $data[$key][3])->first()['id'],
        //     ];
        // }

        $insertArray = [];
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

    // todo : grab this code, it took many seconds to to populate the table

    /**
     * @throws \JsonMachine\Exception\InvalidArgumentException
     */
    public function cityFiller() {  // endoint : city-filler
        $totalCities = 148029;

        ini_set('max_execution_time', 1000);
        $cities = Items::fromFile(__DIR__ . '/cities.json');
        $data = collect();

        foreach ($cities as $key => $city) {
            $data[$key] = [
                'name' => $city->name,
                'state_code' => $city->state_code,
                'latitude' => $city->latitude,
                'longitude' => $city->longitude,
                'wiki_data_id' => $city->wikiDataId,
                'state_id' => State::where('state_code', $city->state_code)->first()['id']
            ];
        }

        foreach ($data->chunk(5000) as $chunk) {
            City::upsert($chunk->toArray(), [
                'state_id'
            ]);
        }
    }

    /**
     * @throws \JsonMachine\Exception\InvalidArgumentException
     */
    public function cityFiller__old() {
        // $totalCities = 148029;
        ini_set('max_execution_time', 1000);
        $cities = Items::fromFile(__DIR__ . '/cities.json', ['decoder' => new ExtJsonDecoder(true)]);
        $data = collect();

        foreach ($cities as $key => $city) {
            $data[$key] = [
                'name' => $city['name'],
                'state_code' => $city['state_code'],
                'latitude' => $city['latitude'],
                'longitude' => $city['longitude'],
                'wiki_data_id' => $city['wikiDataId'],
                'state_id' => State::where('state_code', $city['state_code'])->first()['id']
            ];
        }

        foreach ($data->chunk(2000) as $chunk) {
            City::upsert($chunk->toArray(), [
                'name', 'state_id'
            ], ['name', 'state_code', 'latitude', 'longitude', 'wiki_data_id', 'state_id']);
        }

    }


    // ________________________________________________________________________________________
    // todo : grab this code
    /**
     * @throws \JsonMachine\Exception\InvalidArgumentException
     */
    public function stateFiller() {     // state-filler
        $states = Items::fromFile(__DIR__ . '/states.json');
        $data = [];
        foreach ($states as $state) {
            $data[] = [
                'name' => $state->name,
                'country_code' => $state->country_code,
                'state_code' => $state->state_code,
                'country_id' => Country::where('iso2', $state->country_code)->first()['id'],
                'latitude' => $state->latitude,
                'longitude' => $state->longitude
            ];

        }

        State::upsert($data, [
            'name', 'country_code'
        ], ['name', 'country_code', 'state_code', 'country_id', 'latitude', 'longitude']);

    }

    // todo : pick this statefiller method
    public function stateFiller_old() { // state-filler
        $states = json_decode(file_get_contents(__DIR__ . '/states.json'), true, 512, JSON_THROW_ON_ERROR);

        $data = collect();

        foreach ($states as $state) {
            $data[] = [
                'name' => $state['name'],
                'country_code' => $state['country_code'],
                'state_code' => $state['state_code'],
                'country_id' => Country::where('iso2', $state['country_code'])->first()['id'],
                'latitude' => $state['latitude'],
                'longitude' => $state['longitude']
            ];
        }

        foreach ($data->chunk(500) as $chunk) {
            // \DB::table('states')->updateOrInsert($chunk->toArray());
            State::insert($chunk->toArray());
        }

        foreach ($states as $state) {
            $data[] = [
                'name' => $state['name'],
                'country_code' => $state['country_code'],
                'state_code' => $state['state_code'],
                'country_id' => Country::where('iso2', $state['country_code'])->first()['id'],
                'latitude' => $state['latitude'],
                'longitude' => $state['longitude'],
                // 'created_at' => now()->toDateTimeString(),
                // 'updated_at' => now()->toDateTimeString()
            ];
        }

        $chunks = array_chunk($data, 5000);
        foreach ($chunks as $chunk) {
            State::insert($chunk);
        }
    }

    // todo : pick up this method
    public function insertCountry() {
        $countries = json_decode(file_get_contents(__DIR__ . '/countries.json'), true, 512, JSON_THROW_ON_ERROR);

        foreach ($countries as $country) {
            Country::updateOrCreate([
                'name' => $country['name'],
                'iso3' => $country['iso3'],
                'iso2' => $country['iso2'],
                'numeric_code' => $country['numeric_code'],
                'phone_code' => $country['phone_code'],
                'capital' => $country['capital'],
                'tld' => $country['tld'],
                'native' => $country['native'],
                'region' => $country['region'],
                'subregion' => $country['subregion'],
                'latitude' => $country['latitude'],
                'longitude' => $country['longitude'],
                'flag' => $country['flag']
            ]);
        }
    }

    /**
     * @throws \JsonMachine\Exception\InvalidArgumentException
     */
    public function jsonMachine() { // endpoint : json-machine
        // write your to process json
        $countries = Items::fromFile(__DIR__ . '/countries.json');

        $insert_data = [];

        foreach ($countries as $country) {
            // $posting_date = Carbon::parse($country['Posting_Date']);
            // $posting_date = $posting_date->format('Y-m-d');

            $data = [
                'item_no' => $country['Item_No'],
                'entry_no' => $country['Entry_No'],
                'document_no' => $country['Document_No'],
            ];

            $insert_data[] = $data;
        }

        $insert_data = collect($insert_data); // Make a collection to use the chunk method

        // it will chunk the dataset in smaller collections containing 500 values each.
        // Play with the country to get best result
        $chunks = $insert_data->chunk(500);

        foreach ($chunks as $chunk) {
            \DB::table('items_details')->insert($chunk->toArray());
        }

        // foreach ($countries as $country) {
        //     Country::create([
        //         'name' => $country['name'],
        //         'iso3' => $country['iso3'],
        //         'iso2' => $country['iso2'],
        //         'numeric_code' => $country['numeric_code'],
        //         'phone_code' => $country['phone_code'],
        //         'capital' => $country['capital'],
        //         'tld' => $country['tld'],
        //         'native' => $country['native'],
        //         'region' => $country['region'],
        //         'subregion' => $country['subregion'],
        //         'latitude' => $country['latitude'],
        //         'longitude' => $country['longitude'],
        //         'flag' => $country['flag']
        //     ]);
        // }

        // foreach ($countries as $country) {
        //     // unset($country->id, $country->created_at, $country->updated_at);
        //     $array = json_decode(json_encode($country), true);
        //     // $this->printFormattedData($array);
        // }
    }

    public function accessKey() {   // endpoint : acces-key
        // Setting::set('CURRENCY_LAYER_API_ACCESS_KEY', 'e568ea241bcd6eb1bc61bc9894943f19');
        Setting::set('CURRENCY_LAYER_API_ACCESS_KEY', '');
        $key = Setting::get('CURRENCY_LAYER_API_ACCESS_KEY');
        if (isset($key) && (strlen($key) === 32)) {
            return 'key is set';
        }
        return 'key is not set';
    }

    public function useCarbon() {       // endpoint : carbon
        // CurrencyRate::whereDate('created_at', '<=', Carbon::now()->subDays(1))->delete();
        return Carbon::now()->subDays(5)->diffForHumans();
        // return Currency::firstWhere('created_at');
        // return Currency::first('created_at')->get();
        // return Carbon::canBeCreatedFromFormat(CurrencyRate::first('created_at'), 'H:i:s');
        // return Carbon::now(CurrencyRate::firstWhere('created_at'))->diffForHumans();
        // return Carbon::ATOM;
        // return CurrencyRate::first('created_at');
        // return CurrencyRate::firstWhere('created_at')->get();
        // return Carbon::now()->subDays(5);
        // return Setting::get('rates_save_days');
        // Setting::set('rates_save_days', 5);
        // return Carbon::now()->isWednesday();        // if today is wednesday, it will return TRUE, or 1
        // return Carbon::now()->subDays(4)->diffForHumans();
        // return Carbon::ATOM;
        // return Carbon::now()->diffForHumans();
    }

    public function indexTestMethod() {
        // return Currency::whereCurrencyName('PKR');
        // return Currency::where('currency_name', 'PKR');
        // return Currency::where('currency',  'PKR')->id;
        // return Currency::where('currency', 'PKR')->first()->id;     // returns 167
        return Currency::firstWhere('currency', '=', substr('USDPKR', '3'))->id;
    }

    public function getCurrencyLayerResponseViaOtifCurl() {
        try {
            $response = json_decode(Curl::Make()
                ->GET
                ->url('http://api.currencylayer.com/live')
                ->params([
                    'access_key' => Setting::get('mesonet_api_token')
                ])
                ->execute(), true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $exception) {
            return response()->json([
                'message' => 'Error Fetching Weather',
                'description' => $exception->getMessage()
            ], 400);
        }
        return $response;
    }

    /**
     * hit currency layer api and get the result, and insert the results into the currency_rates table,
     * create loop that cycles the currencyNames like USDPKR, and removes USD from them, pick the PKR
     * and compare to the currencies table where PKR = PKR and => id, and by getting the id, insert this
     * id into the currency_rates table colomn named as "currency_id", and currency_id is unique for all
     * countries, we are not getting data for all countries but for specific countries like 20 or 30
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCurrencyLayerApiResultAndFillTable() {
        // this code is only for apiJson to get reponse, not for the artisan commands

        try {
            $response = json_decode(
                Http::get('http://api.currencylayer.com/live', [
                    'access_key' => env('CURRENCY_LAYER_API_ACCESS_KEY')
                ]), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => 'cannot fetch the data',
                'exceptionCode' => (int)($exception->getCode()),
                'description' => $exception->getMessage()
            ], 400);
        }

        foreach ($response['quotes'] as $i => $value) {
            $currencyId = Currency::firstWhere('currency', '=', substr($i, '3'));
            if ($currencyId) {
                CurrencyRate::updateOrCreate([
                    'currency_id' => $currencyId->id,
                    'usd_rates' => $value
                ]);
            }
        }
    }

    /**
     *  reads the csv file, and show all the countries details in the formatted manner, change csv file first,
     *  by default it is set only the part of long csv file
     */
    public function showCountries() {
        $data = [];

        // files moved to the public folder, get public_path() to access
        if (($open = fopen(__DIR__ . '/countries.csv', "r + b")) !== FALSE) {
            while (($student = fgetcsv($open, NULL, ",")) !== FALSE) {
                $data[] = $student;
            }

            fclose($open);
        }

        $this->printFormattedData($data);
    }

    public function fillCountries() {
        $countriesData = [];

        if (($open = fopen(__DIR__ . '/countries.csv', "r + b")) !== FALSE) {
            while (($singleRecord = fgetcsv($open, NULL, ",")) !== FALSE) {
                $countriesData[] = $singleRecord;
            }

            fclose($open);
        }

        $countriesData = array_slice($countriesData, '1');  // slice the first row, colomnNames

        foreach ($countriesData as $singleRecord) {
            Country::create([   // we can use updateOrCreate() method here
                'name' => $singleRecord[1],
                'iso3' => $singleRecord[2],
                'iso2' => $singleRecord[3],
                'numeric_code' => $singleRecord[4],
                'phone_code' => $singleRecord[5],
                'capital' => $singleRecord[6],
                'tld' => $singleRecord[10],
                'native' => $singleRecord[11],
                'region' => $singleRecord[12],
                'subregion' => $singleRecord[13],
                'latitude' => $singleRecord[15],
                'longitude' => $singleRecord[16],
                // 'flag' => $singleRecord['address'],
            ]);
        }

        // $entriesLimit = count($countriesData);
        // for($i = 1; $i <$entriesLimit; $i++) {
        //     Country::create([
        //         'name' => $singleRecord['first_name'],
        //         'iso3' => $singleRecord['last_name'],
        //         'iso2' => $singleRecord['email'],
        //         'gender' => $singleRecord['gender'],
        //         'numeric_code' => $singleRecord['numeric_code'],
        //         'phone_code' => $singleRecord['address'],
        //         'capital' => $singleRecord['address'],
        //         'tld' => $singleRecord['address'],
        //         'native' => $singleRecord['address'],
        //         'region' => $singleRecord['address'],
        //         'subregion' => $singleRecord['address'],
        //         'latitude' => $singleRecord['address'],
        //         'longitude' => $singleRecord['address'],
        //         // 'flag' => $singleRecord['address'],
        //     ]);
        // }

        // $this->printFormattedData($countriesData);

    }

    public function index(): void {
        // dd(__DIR__ . '/cities.csv'); // nothing shows in response
        // dd(public_path());
        // dd(resource_path());
        // dd(storage_path());

        $students = [];

        // if (($open = fopen(storage_path() . "/students.csv", "r")) !== FALSE) {
        if (($open = fopen(__DIR__ . '/test.csv', "r + b")) !== FALSE) {
            while (($data = fgetcsv($open, NULL, ",")) !== FALSE) {
                $students[] = $data;
            }
            fclose($open);
        }

        $this->printFormattedData($students);
    }

    public function w3schoolConvertMethodCSV(): void {
        // dd(__DIR__ . '/' . 'cities.csv');

        $file = fopen(__DIR__ . '/test.csv', "r + b");

        while (!feof($file)) {
            echo "<pre>";

            $data = fgetcsv($file);

            for ($i = 0; $i <= 10; $i++) {
                // echo $data['id'] . '<br>';  // undefined array key 'id'
                // echo $data[$i] . '<br>'; // prints record one by one
                //print_r($data[$i]);

                $id = $data[$i][0];
                $name = $data[$i][1];
                $stateId = $data[$i][2];

                echo $name . '<br>';
            }
            //print_r(fgetcsv($file));
        }

        fclose($file);
    }

    public function thirdMethod(): void {
        $data = [];

        // if (($open = fopen(storage_path() . "/students.csv", "r")) !== FALSE) {
        if (($open = fopen(__DIR__ . '/test.csv', "r + b")) !== FALSE) {
            while (($student = fgetcsv($open, NULL, ",")) !== FALSE) {
                $data[] = $student;
            }

            fclose($open);
        }

        echo "<pre>";
        foreach ($data as $student) {
            print_r($student);
        }
    }

    public function readJsonFile(): void {
        $data = json_decode(file_get_contents(__DIR__ . '/sample.json'), true, 512, JSON_THROW_ON_ERROR);
        $this->printFormattedData($data);
    }

    public function fillTableJson(): void {
        $data = json_decode(file_get_contents(__DIR__ . '/sample.json'), true, 512, JSON_THROW_ON_ERROR);

        foreach ($data as $singleRecord) {
            SampleModel::create([
                'first_name' => $singleRecord['first_name'],
                'last_name' => $singleRecord['last_name'],
                'email' => $singleRecord['email'],
                'gender' => $singleRecord['gender'],
                'ip_address' => $singleRecord['ip_address'],
                'address' => $singleRecord['address']
            ]);
        }
    }

    /**
     * get all the flag names from countries.json file, and fill into the countires table where colomn is "flag"
     */
    public function fillFlagsInCountriesTable() {
        $data = json_decode(file_get_contents(__DIR__ . '/countries.json'), true, 512, JSON_THROW_ON_ERROR);
        $dataRows = count($data);
        foreach ($data as $singleRecord) {
            Country::create([
                'flag' => $singleRecord['flag'],
            ]);
        }
    }

    /**
     * reads the csv file of 148k lines, create chunks of the lines, insert that chunk into
     * table one by one, good approach, otherwise if we directly apply loop over it,
     * the time will it take would be very long, tayyab bhai has helped me to optimize this,
     * now remember, do not set_time_limit($anyLimit) to any number, by default it is 30 seconds
     */
    public function fillCitiesTable() {
        // set_time_limit(0);
        $filename = __DIR__ . '/cities.csv';
        $rows = $this->rowsCountCsv($filename);
        $itemsPerRun = 50000;
        for ($i = 0; $i <= $rows; $i += $itemsPerRun + 1) {
            $cities = array();
            $chunk = $this->csvSlice($filename, $i, $itemsPerRun);
            $start = Carbon::now();
            foreach ($chunk as $item) {
                $cities[] = [
                    'name' => $item->name,
                    'state_code' => $item->state_code,
                    'state_name' => $item->state_name,
                    'country_code' => $item->country_code,
                    'country_name' => $item->country_name,
                    'latitude' => $item->latitude,
                    'longitude' => $item->longitude,
                    'wikiDataId' => $item->wikiDataId
                ];

                /*City::create([
                    'name' => $item->name,
                    'state_code' => $item->state_code,
                    'state_name' => $item->state_name,
                    'country_code' => $item->country_code,
                    'country_name' => $item->country_name,
                    'latitude' => $item->latitude,
                    'longitude' => $item->longitude,
                    'wikiDataId' => $item->wikiDataId
                ]);*/
                // echo "$i - item category = " . $item->CurrentURL . "\n"; //Note CurrentURL is a case sensitive
            }

            //City::insert($cities);
            foreach (array_chunk($cities, 5000) as $part) {
                City::upsert(
                    $part, ['wikiDataId']
                );
            }

            $end = Carbon::now();
            dd($end->diffForHumans($start));
        }

        //  ini_set('max_execution_time', 180); //3 minutes

        // $data = [];
        // if (($open = fopen(__DIR__ . '/cities.csv', "r + b")) !== FALSE) {
        //     while (($student = fgetcsv($open, NULL, ",")) !== FALSE) {
        //         $data[] = $student;
        //     }
        //     fclose($open);
        // }

        // $numCsvRecords = count($data);
        // for ($i = 1; $i < $numCsvRecords; $i++) {
        //     State::create([
        //         'name' => $data[$i][1],
        //         'country_code' => $data[$i][3],
        //         'country_name' => $data[$i][4],
        //         'state_code' => $data[$i][5],
        //         'latitude' => $data[$i][7],
        //         'longitude' => $data[$i][8],
        //     ]);
        // }
    }

    // it does not matter anyone who comes to me, or anyone who leaves me

    private function csvSlice($filename, $start, $desiredCount) {
        $row = 0;
        $count = 0;
        $rows = array();
        if (($handle = fopen($filename, 'rb')) === FALSE) {
            return FALSE;
        }
        while (($rowData = fgetcsv($handle, 2000, ",")) !== FALSE) {
            // Grab headings.
            if ($row === 0) {
                $headings = $rowData;
                $row++;
                continue;
            }

            // Not there yet.
            if ($row++ < $start) {
                continue;
            }

            $rows[] = (object)array_combine($headings, $rowData);
            $count++;
            if ($count == $desiredCount) {
                return $rows;
            }
        }
        return $rows;
    }

    public function fillCitiesTable_old() {
        $data = [];
        // we have put the csv files into public folder
        if (($open = fopen(__DIR__ . '/cities.csv', "r + b")) !== FALSE) {
            while (($student = fgetcsv($open, NULL, ",")) !== FALSE) {
                $data[] = $student;
            }
            fclose($open);
        }

        // dd($data);

        $numCsvRecords = count($data);
        for ($i = 1; $i < $numCsvRecords; $i++) {
            City::create([
                'name' => $data[$i][1],
                'state_code' => $data[$i][3],
                'state_name' => $data[$i][4],
                'country_code' => $data[$i][6],
                'country_name' => $data[$i][7],
                'latitude' => $data[$i][8],
                'longitude' => $data[$i][9],
                'wikiDataId' => $data[$i][10],
            ]);
        }
    }

    public function currencyNameFromCountryTable(): void {
        $data = [];

        if (($open = fopen(__DIR__ . '/countries.csv', 'r + b')) !== FALSE) {
            while (($student = fgetcsv($open, NULL, ',')) !== FALSE) {
                $data[] = $student;
            }
            fclose($open);
        }

        $recordCounter = count($data);
        for ($i = 1; $i < $recordCounter; $i++) {
            Currency::create([
                'currency' => $data[$i][7],
                'currency_name' => $data[$i][8],
                'currency_symbol' => $data[$i][9],
            ]);
        }
    }

    public function readTimezoneFromCountiesCsv(): void {
        $data = [];

        // if (($open = fopen(storage_path() . "/students.csv", "r")) !== FALSE) {
        if (($open = fopen(__DIR__ . '/countries.csv', 'r + b')) !== FALSE) {
            while (($student = fgetcsv($open, NULL, ',')) !== FALSE) {
                $data[] = $student;
            }
            fclose($open);
        }

        // $this->printFormattedData($data[1][14]);     // json
        // $this->printFormattedData($data[14]);    // 14th record
        // dd(count($data));    // 251

        // starting the loop from 1, because the 1st record is of colomn names
        for ($i = 1; $i <= 10; $i++) {
            // $this->printFormattedData($data[$i][14]);
            // print_r(json_decode($data[$i][14], false, 512, JSON_THROW_ON_ERROR));
            // print_r($data[$i][14]);
            // $anotherData = json_decode($data[$i][14]);
            // $anotherData = json_decode($data[$i][14]);
            // $this->printFormattedData($anotherData);

            $jsonData = $data[$i][14];
            // $subData = json_decode($jsonData);
            $timezone = explode(",", $this->longJson);

            // dd(substr($timezone[0],strpos($timezone[0],':')+2),$jsonData);
            dd(explode(":", str_replace(['[', '{', '}', ']'], '', $timezone[79])), $timezone);
            // $this->printFormattedData($subData);
        }
    }

    public function exportedTimezonesInsertion() {
        // endpoint => exported-timezones-insert
        // migration => 2022_05_16_120316_create_one_timezones_table.php
        // table => one_timezones
        // mode OneTimezone

        // $timezones = json_decode($this->exportedTimezonesJson);  // Typed property App\Http\Controllers\TestController::$exportedTimezonesJson must not be accessed before initialization
        $json = File::get(database_path('jsons/exported_timezones.json'));
        $timezones = json_decode($json);

        $data = [];
        $ids = [];
        foreach ($timezones as $key => $timezone) {
            $obj = OneTimezone::updateOrCreate([
                'name' => $timezone->name,
                'gmt_offset' => $timezone->gmt_offset,
                'gmt_offset_name' => $timezone->gmt_offset_name,
                'abbreviation' => $timezone->abbreviation,
                'tz_name' => $timezone->tz_name
            ]);

            $ids[] = $obj->id;

        }

    }




    // THIS METHOD IS USED IN SEEDERS, RE-CHECK IT
    // filltimeozne method from csv file, this method is so messy,
    // so we created another method that reads from json file, use that appreach instead of this
    public function timezoneSeeder() {  // endpoint => timezone-seeder
        ini_set('max_execution_time', 1000);
        $data = [];
        if (($open = fopen(public_path('csvJsonFiles/countries.csv'), 'r + b')) !== FALSE) {
            while (($timezone = fgetcsv($open, NULL, ',')) !== FALSE) {
                $data[] = $timezone;
            }
            fclose($open);
        }

        $data = $this->replaceChars($data, ['[', ']']);
        $data = $this->replaceChars($data, (array)'\/', ' - ');
        $dataNumRecords = count($data);

        $resultingArray = [];
        $subArray = [];

        for ($i = 1; $i < $dataNumRecords; $i++) {
            $resultingArray[$data[$i][1]] = ($this->fixJson($data[$i][14]));
        }

        $jsonData = [];
        foreach ($resultingArray as $singleString) {
            if (is_null(json_decode($singleString))) {
                $singleString .= '}';
            }
            $jsonData[] = json_decode($singleString);
        }

        foreach ($resultingArray as $key => $jsonData) {

            if (str_contains($resultingArray[$key], '},')) {  // this line means that certain country does have multiple timezones
                $subArray[] = $this->fixJsonString(explode('},', $resultingArray[$key]));
                unset($resultingArray[$key]);
            } else {
                $subArray[] = $jsonData;
            }

            $countryObj = Country::where('name', $key)->first();

            $ids = [];
            foreach ($subArray as $timezone) {
                if (is_string($timezone)) {     // if string, then  there is single timezone
                    $ids[] = $this->insertTimezone($timezone);
                } else {    // if array, then there are muliple timezones
                    $timezones = $this->fixJsonString($timezone);
                    foreach ($timezones as $singleTimezone) {
                        $ids[] = $this->insertTimezone($singleTimezone);
                    }
                }
            }
            $countryObj->timezones()->sync($ids);
        }
    }

    // under construction method, cannot be used in production now
    public function timezone() {  // fill-zone
        $data = [];

        if (($open = fopen(public_path('csvJsonFiles/countries.csv'), 'r + b')) !== FALSE) {
            while (($singleRecord = fgetcsv($open, NULL, ',')) !== FALSE) {
                $data[] = $singleRecord;
            }
            fclose($open);
        }

        $data = $this->replaceChars($data, ['[', ']']);
        $data = $this->replaceChars($data, (array)'\/', ' - ');
        $totalRecords = count($data);

        $resultingArray = [];
        $subArray = [];
        for ($i = 1; $i < $totalRecords; $i++) {
            $resultingArray[$i] = ($this->fixJson($data[$i][14]));
            if (str_contains($resultingArray[$i], '},')) {
                $subArray[] = $this->fixJsonString(explode('},', $resultingArray[$i]));
                unset($resultingArray[$i]);
            }
        }

        foreach ($subArray as $value) {
            foreach ($value as $singleArray) {
                $resultingArray[] = $singleArray;
            }
        }

        $index221 = json_decode($resultingArray[221]);
        $index231 = json_decode($resultingArray[231]);

        $jsonData = [];
        foreach ($resultingArray as $singleString) {
            if (is_null(json_decode($singleString))) {
                $singleString .= '}';
            }
            $jsonData[] = json_decode($singleString);
        }

        $jsonData[221] = $index221;
        $jsonData[231] = $index231;

        foreach ($jsonData as $singleRecord) {

            $singleRecord = (array)($singleRecord);
            SampleModel::create([
                'zone_name' => $singleRecord['zoneName'],
                'gmt_offset' => $singleRecord['gmtOffset'],
                'gmt_offset_name' => $singleRecord['gmtOffsetName'],
                'abbreviation' => $singleRecord['abbreviation'],
                'tz_name' => $singleRecord['tzName'],
            ]);
        }
    }

    public function playWithRandomNumbers() {
        return view('pract', ['numbers' => $this->getArrayOfRandomNumbers(20, 99, 20)]);
    }

}
