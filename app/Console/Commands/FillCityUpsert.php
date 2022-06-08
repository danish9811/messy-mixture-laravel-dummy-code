<?php

namespace App\Console\Commands;

use App\Models\City;
use Illuminate\Console\Command;

class FillCityUpsert extends Command {

    protected $signature = 'fill:city';

    protected $description = 'Command description';

    public function handle() {

        ini_set('memory_limit', '256M');
        $totalCities = 148249;
        $filename = storage_path('newCsvFiles/cities.csv');
        $rows = $this->rowsCountCsv($filename);
        $itemsPerRun = 50000;
        for ($i = 0; $i <= $rows; $i += $itemsPerRun + 1) {
            $cities = [];
            $chunk = $this->csvSlice($filename, $i, $itemsPerRun);
            foreach ($chunk as $item) {

                $cities[] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'latitude' => $item->latitude,
                    'longitude' => $item->longitude,
                    'wiki_data_id' => $item->wikiDataId,
                    'state_id' => $item->state_id
                ];

            }

            foreach (array_chunk($cities, 5000) as $part) {
                City::upsert(
                    $part, ['id']
                );
            }
        }

        return 0;
    }

    private function rowsCountCsv($filename) {
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
}
