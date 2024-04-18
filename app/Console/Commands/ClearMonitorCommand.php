<?php

namespace App\Console\Commands;

use Database\Seeders\ServicesSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

// Delete before deploy to github
class ClearMonitorCommand extends Command
{
    protected $signature = 'clear:monitor';

    protected $description = 'Command description';

    public function handle(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('service_log_launches')->truncate();
        DB::table('service_logs')->truncate();
        DB::table('services')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        (new ServicesSeeder())->run();
    }
}
