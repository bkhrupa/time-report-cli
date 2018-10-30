<?php

namespace App\Commands\Report;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;

class MakeStubs extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'report:make-stubs';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        foreach (File::allFiles(app_path('Systems')) as $file) {
            $system = str_replace('.php', '', $file->getFilename());
            // Systems/CodebaseLemberg.php -> codebase-lemberg
            $dir = Str::kebab($system);

            if (!File::exists($dir)) {
                File::makeDirectory($dir);
            }

            $stubFile = 'dummy-project-name.csv';
            File::copy(
                base_path('stubs' . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $stubFile),
                base_path('reports' . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $stubFile)
            );


            $this->info('Stub CSV file created for system "' . $system . '"');
        }
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
