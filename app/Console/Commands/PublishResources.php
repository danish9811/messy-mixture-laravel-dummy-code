<?php

namespace App\Console\Commands;

use Artisan;
use Illuminate\Console\Command;

class PublishResources extends Command {

    protected $signature = 'publish:resources';

    protected $description = 'Publish all the resources of currency layer package into your project';

    public function handle() {

        //Artisan::call('php artisan vendor:publish --tag=otif-flags');

        // when we call artisan froma anywhere other than artisan console, we don't write 'php artisan'
        Artisan::call('vendor:publish --tag=otif-flags');

        $this->line('flags published to public/flags');
        $this->newLine();

        return 0;
    }
}
