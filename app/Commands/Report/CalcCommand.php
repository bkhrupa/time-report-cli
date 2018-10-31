<?php

namespace App\Commands\Report;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;

class CalcCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'report:calc';

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
        $totalMinutes = 0;

        foreach (File::allFiles(base_path('reports')) as $file) {
            $system = explode(DIRECTORY_SEPARATOR, $file->getRelativePathname())[0];
            $project = str_replace('.csv', '', $file->getFilename());
            $projectTotalMinutes = 0;

            $this->comment('Project: ' . $project . ' (' . $system . ')');

            foreach (file($file->getPathname()) as $line) {
                if (!empty($line)) {
                    $row = explode(';', $line);

                    $row = array_map(function ($item) {
                        return trim($item);
                    }, $row);
                    list($minutes, $ticket, $message) = $row;

                    $this->line($minutes . "m\t" . $ticket . "\t" . $message);
                    $totalMinutes = $totalMinutes + (integer)$minutes;
                    $projectTotalMinutes = $projectTotalMinutes + (integer)$minutes;
                }
            }

            $this->info('Total by "' . $project . '": ' . $this->minutesToHourString($projectTotalMinutes));
            $this->line('');
        }

        $this->alert('Total: ' . $this->minutesToHourString($totalMinutes) . ' (' . $totalMinutes . ') minutes');
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

    protected function minutesToHourString($int)
    {
        $hours = floor($int / 60);
        $minutes = $int % 60;

        return $hours . 'h ' . $minutes . 'm';
    }
}
