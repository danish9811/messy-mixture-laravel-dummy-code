<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\Timezone;
use Illuminate\Console\Command;

class RunSeedersWithProgress extends Command {
    protected $signature = 'seed:proceed';
    protected $description = 'Command description';

    public function handle() {

        // TIMEZONE SEEDER STARTING NOW

        $timezoneProgressBar = $this->output->createProgressBar(428);
        $this->line('timezone seeder started');
        $this->newLine();
        $timezoneProgressBar->start();

        ini_set('max_execution_time', 1000);
        $data = [];
        if (($open = fopen(database_path('csvs/countries.csv'), 'r + b')) !== FALSE) {
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

            foreach ($subArray as $timezone) {
                $ids = [];
                if (is_string($timezone)) {     // if string, then  there is single timezone
                    $ids[] = $this->insertTimezone($timezone);
                } else {    // if array, then there are muliple timezones
                    $timezones = $this->fixJsonString($timezone);
                    foreach ($timezones as $singleTimezone) {
                        $ids[] = $this->insertTimezone($singleTimezone);
                    }
                }

            }

            $timezoneProgressBar->finish();
            $countryObj->timezones()->sync(($ids ?? []));
        }

        $this->newLine();
        $this->line('timezone seeder completed');
        $this->newLine();

        return 0;
    }   // end of Command::handle method

    private function insertTimezone(string $timezone) {
        $timezone = json_decode($timezone);
        $timezoneObj = Timezone::updateOrCreate(['name' => $timezone->zoneName], [
            'name' => $timezone->zoneName,
            'gmt_offset' => $timezone->gmtOffset,
            'gmt_offset_name' => $timezone->gmtOffsetName,
            'abbreviation' => $timezone->abbreviation,
            'tz_name' => $timezone->tzName,
        ]);

        return $timezoneObj['id'];
    }

    private function replaceChars($hayStack, array $charsArray, $character = ''): array {
        $tempArray = [];
        foreach ($hayStack as $item) {
            $tempArray[] = str_replace($charsArray, $character, $item);
        }
        return $tempArray;
    }

    private function fixJson(string $str): string {
        return preg_replace(
            '/(?<=(\{|\,))(\w+)(?=\:)/',
            '"$2"',
            str_replace("'", '"', $str)
        );
    }

    private function fixJsonString(array $stringsArray): array {
        foreach ($stringsArray as $index => $string) {
            if (!(substr($string, -1) === '}')) {
                $stringsArray[$index] = $string . '}';
            }
        }
        return $stringsArray;
    }

}
