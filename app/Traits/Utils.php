<?php

namespace App\Traits;

use App\Models\Timezone;
use Exception;

trait Utils {

    public string $countriesJson;
    public string $countriesCsv;
    public string $countriesJsonOrg;
    public string $statesJson;
    public string $statesCsv;
    public string $citiesJson;
    public string $citiesCsv;
    public string $exportedTimezonesJson;
    public string $exportedCitiesJson;
    public string $exportedStatesjson;
    private string $longJson = '[{zoneName:\'America\/Araguaina\',gmtOffset:-10800,gmtOffsetName:\'UTC-03:00\',abbreviation:\'BRT\',tzName:\'Bras\u00edlia Time\'},{zoneName:\'America\/Bahia\',gmtOffset:-10800,gmtOffsetName:\'UTC-03:00\',abbreviation:\'BRT\',tzName:\'Bras\u00edlia Time\'},{zoneName:\'America\/Belem\',gmtOffset:-10800,gmtOffsetName:\'UTC-03:00\',abbreviation:\'BRT\',tzName:\'Bras\u00edlia Time\'},{zoneName:\'America\/Boa_Vista\',gmtOffset:-14400,gmtOffsetName:\'UTC-04:00\',abbreviation:\'AMT\',tzName:\'Amazon Time (Brazil)[3\'},{zoneName:\'America\/Campo_Grande\',gmtOffset:-14400,gmtOffsetName:\'UTC-04:00\',abbreviation:\'AMT\',tzName:\'Amazon Time (Brazil)[3\'},{zoneName:\'America\/Cuiaba\',gmtOffset:-14400,gmtOffsetName:\'UTC-04:00\',abbreviation:\'BRT\',tzName:\'Brasilia Time\'},{zoneName:\'America\/Eirunepe\',gmtOffset:-18000,gmtOffsetName:\'UTC-05:00\',abbreviation:\'ACT\',tzName:\'Acre Time\'},{zoneName:\'America\/Fortaleza\',gmtOffset:-10800,gmtOffsetName:\'UTC-03:00\',abbreviation:\'BRT\',tzName:\'Bras\u00edlia Time\'},{zoneName:\'America\/Maceio\',gmtOffset:-10800,gmtOffsetName:\'UTC-03:00\',abbreviation:\'BRT\',tzName:\'Bras\u00edlia Time\'},{zoneName:\'America\/Manaus\',gmtOffset:-14400,gmtOffsetName:\'UTC-04:00\',abbreviation:\'AMT\',tzName:\'Amazon Time (Brazil)\'},{zoneName:\'America\/Noronha\',gmtOffset:-7200,gmtOffsetName:\'UTC-02:00\',abbreviation:\'FNT\',tzName:\'Fernando de Noronha Time\'},{zoneName:\'America\/Porto_Velho\',gmtOffset:-14400,gmtOffsetName:\'UTC-04:00\',abbreviation:\'AMT\',tzName:\'Amazon Time (Brazil)[3\'},{zoneName:\'America\/Recife\',gmtOffset:-10800,gmtOffsetName:\'UTC-03:00\',abbreviation:\'BRT\',tzName:\'Bras\u00edlia Time\'},{zoneName:\'America\/Rio_Branco\',gmtOffset:-18000,gmtOffsetName:\'UTC-05:00\',abbreviation:\'ACT\',tzName:\'Acre Time\'},{zoneName:\'America\/Santarem\',gmtOffset:-10800,gmtOffsetName:\'UTC-03:00\',abbreviation:\'BRT\',tzName:\'Bras\u00edlia Time\'},{zoneName:\'America\/Sao_Paulo\',gmtOffset:-10800,gmtOffsetName:\'UTC-03:00\',abbreviation:\'BRT\',tzName:\'Bras\u00edlia Time\'}]';

    public function __construct() {
        $this->countriesJson = database_path('jsons/countries.json');
        $this->countriesJsonOrg = database_path('jsons/countries-org.json');
        $this->countriesCsv = database_path('csvs/countries.csv');
        $this->statesJson = database_path('jsons/states.json');
        $this->statesCsv = database_path('csvs/states.csv');
        $this->citiesCsv = database_path('csvs/cities.csv');
        $this->citiesJson = database_path('jsons/cities.json');
        $this->exportedTimezonesJson = database_path('jsons/exported_timezones.json');

    }

    /**
     * Created and array of random numbers of given size and all the numbers will be in the given upper and
     * lower limits
     * @param int $lowerLimit
     * @param int $upperLimit
     * @param int $arraySize
     * @return array
     * @throws Exception
     */
    public function getArrayOfRandomNumbers(int $lowerLimit = 10, int $upperLimit = 90, int $arraySize = 10): array {
        $numbersArray = [];
        for ($i = 1; $i <= $arraySize; $i++) {
            $numbersArray[] = random_int($lowerLimit, $upperLimit);
        }
        return $numbersArray;
    }

    /**
     * Array of filled values with table number, like if you want an array of table 2, the returned array will be
     * [2, 4, 6, 8, ..... 20], the table default limit is 10, it means there will be 10 values in the array
     * @param int $tableNum
     * @param int $tableLimit
     * @return array
     */
    public function getTableArray(int $tableNum, int $tableLimit = 10): array {
        $tableArray = [];
        for ($i = 1; $i <= $tableLimit; $i++) {
            $tableArray[] = $i * $tableNum;
        }
        return $tableArray;
    }

    /**
     * Counts how many NULL values in the array and return it
     * @param array $array
     * @return int
     */
    public function nullCounter(array $array): int {
        $nullCounter = 0;
        foreach ($array as $item) {
            if (is_null($item)) {
                $nullCounter++;
            }
        }
        return $nullCounter;
    }

    public function insertTimezone(string $timezone) {
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

    /**
     * Repairs the string to properly parse as json, accepts and aray and the subArray consists on strings,
     * if the string does not have '}' at the end, fixes this and return the whole array with fixed values
     * @param array $stringsArray
     * @return array
     */
    public function fixJsonString(array $stringsArray): array {
        foreach ($stringsArray as $index => $string) {
            if (!(substr($string, -1) === '}')) {
                $stringsArray[$index] = $string . '}';
            }
        }
        return $stringsArray;
    }

    public function replaceChars($hayStack, array $charsArray, $character = ''): array {
        $tempArray = [];
        foreach ($hayStack as $item) {
            $tempArray[] = str_replace($charsArray, $character, $item);
        }
        return $tempArray;
    }

    public function printFormattedData($data): void {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }

    public function rowsCountCsv($filename) {
        ini_set('auto_detect_line_endings', TRUE);
        $countRows = 0;
        if (($handle = fopen($filename, 'rb')) !== FALSE) {
            while (($row_data = fgetcsv($handle, 2000, ",")) !== FALSE) {
                $countRows++;
            }
            fclose($handle);
            // Exclude the headings.
            $countRows--;
            return $countRows;
        }
    }

    /**
     * remove certain characters from the strings inside the array, for example an array has strings,
     * and we want to remove certain or set of characters and replace by another character or string,
     * like ['one dark', 'darkula', 'oceanic next'] , we want to remove 'a' or replace 'a' by 'c'
     * @param $hayStack
     * @param array $charsArray
     * @param string $character
     * @return array the array with replace strings
     */
    public function removeCharacters($hayStack, array $charsArray, $character = ''): array {
        $tempArray = [];
        foreach ($hayStack as $item) {
            $tempArray[] = str_replace($charsArray, $character, $item);
        }
        return $tempArray;
    }

    public function cutString(string $string): string {
        $startIndex = strpos($string, '{');
        $endIndex = strpos($string, '}');
        return substr($string, $startIndex, ($endIndex - $startIndex) + 1);
    }

    public function fixJson(string $str): string {
        return preg_replace(
            '/(?<=(\{|\,))(\w+)(?=\:)/',
            '"$2"',
            str_replace("'", '"', $str) // may not work properly, if values may contain apostroph symbols, but seems not actual for your case
        );
    }

    public function countChars($string, $char): int {
        $length = strlen($string);
        $counter = 0;
        for ($i = 0; $i < $length; $i++) {
            if ($string[$i] === $char) {
                $counter++;
            }
        }
        return $counter;
    }

    public function singleString($string): string {
        $startChar = strpos($string, '{');
        $endChar = strpos($string, '}');
        return substr($string, $startChar, ($endChar - $startChar + 1));
    }

    public function getNullIndexes(array $array): array {
        $nullIndexes = [];
        foreach ($array as $i => $iValue) {
            if (is_null($iValue)) {
                $nullIndexes[] = $i;
            }
        }
        return $nullIndexes;
    }

    public function seperateStrings($string): array {
        $counter = strlen($string);
        $finalArr = [];
        for ($i = 0; $i < $counter; $i++) {
            $startChar = strpos($string, '{');
            $endChar = strpos($string, '}');
            $finalArr[] = substr($string, $startChar, ($endChar - $startChar + 1));
        }
        return $finalArr;
    }

    public function isArrayUnique(array $array): bool {
        $counts = array_count_values($array);
        array_filter($array, static function ($value) use ($counts) {
            return $counts[$value] > 1;
        });
    }

    /**
     * to check if the array has duplicates or not
     * @param $array
     * @return bool
     */
    public function hasDuplicates($array) {
        $dupe_array = array();
        foreach ($array as $val) {
            if (++$dupe_array[$val] > 1) {
                return true;
            }
        }
        return false;
    }

    /**
     * finds the minimum number in the array and returns it, if array is blank, returns -1<br>
     * php has built-in method called min($var1, $var2 ...... $var_n)
     * @param array $numbersArray the array where we are searching
     * @return int the minimum value
     */
    public function getMin(array $numbersArray) : int {
        if (empty($numbersArray)) {
            return -1;
        }

        $temp = 0;
        foreach($numbersArray as $item) {
            $temp = $item;
            if ($temp < $item) {
                $temp = $item;
            }
        }

        return $temp;
    }

    /**
     * finds the maximum number in the array, and returns it, if the array is empty, return -1 <br>
     * php has built-in method called max($var1, $var2 ...... $var_n)
     * @param array $numbersArray the array where we are searching
     * @return int the maximum value
     */
    public function getMax(array $numbersArray) : int {
        if (empty($numbersArray)) {
            return -1;
        }

        $temp = 0;
        foreach($numbersArray as $item) {
            $temp = $item;
            if ($temp > $item) {
                $temp = $item;
            }
        }

        return $temp;

    }



}