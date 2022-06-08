<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestCommand extends Command {

    protected $signature = 'test:command';

    protected $description = 'sample command for check';

    public function handle() {

        $number = 500;

        $progressBar = $this->output->createProgressBar(5);
        $progressBar->start();

        $tableArr = [];
        for ($i = 1; $i <= $number; $i++) {
            $tableArr[] = 5 * $i;
            $progressBar->advance();
        }

        $progressBar->finish();

        $this->newLine(2);

        return 0;
    }
}
