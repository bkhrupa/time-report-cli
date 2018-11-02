<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ReportCommand extends Command
{

    const ACTION_CALC = 'calc';
    const ACTION_SEND = 'send';
    const ACTION_MAKE_STUBS = 'make-stubs';

    static protected $actions = [
        self::ACTION_CALC,
        self::ACTION_SEND,
        self::ACTION_MAKE_STUBS,
    ];

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $name = 'report';

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
        // Command Experiments
//        if (!$this->hasOption('force') || !$this->option('force')) {
//            $this->error(' already exists!');
//
//            return false;
//        }
        $action = $this->argument('action');

        switch ($action) {
            case self::ACTION_SEND:
                $this->processTimeSessions(true);
                break;
            case self::ACTION_MAKE_STUBS:
                $this->makeStubs();
                break;
            case self::ACTION_CALC:
            default:
                $this->processTimeSessions();
                break;
        }
    }

    protected function processTimeSessions(bool $isNeedToSend = false)
    {
        $totalMinutes = 0;

        foreach (File::allFiles(base_path('reports')) as $file) {
            $system = explode(DIRECTORY_SEPARATOR, $file->getRelativePathname())[0];
            $project = str_replace('.csv', '', $file->getFilename());
            $projectTotalMinutes = 0;

            $this->line('');
            $this->line('Project: <comment>' . $project . '</comment> (<bg=default;fg=blue>' . $system . '</>)');

            $rows = file($file->getPathname());

            if ($isNeedToSend) {
                $this->info('Starting send for project ' . $project . '. Tickets count ' . count($rows));
                $this->output->progressStart(count($rows));
            }

            foreach ($rows as $line) {
                if (!empty($line)) {
                    $row = explode(';', $line);

                    $row = array_map(function ($item) {
                        return trim($item);
                    }, $row);
                    list($minutes, $ticket, $message) = $row;

                    if (!$isNeedToSend) {
                        $this->line($minutes . "m\t" . $ticket . "\t" . $message);
                    }
                    $totalMinutes = $totalMinutes + (integer)$minutes;
                    $projectTotalMinutes = $projectTotalMinutes + (integer)$minutes;

                    if ($isNeedToSend) {
                        $this->output->progressAdvance(1);
                        sleep(1);
                    }
                }
            }

            if (!$isNeedToSend) {
                $this->line('');
                $this->info('Total by "' . $project . '": ' . $this->minutesToHourString($projectTotalMinutes));
            }
        }

        $this->line('');
        $this->alert('Total: ' . $this->minutesToHourString($totalMinutes) . ' (' . $totalMinutes . ') minutes');
    }

    public function makeStubs()
    {
        foreach (File::allFiles(app_path('Systems')) as $file) {
            $system = str_replace('.php', '', $file->getFilename());
            // Systems/CodebaseLemberg.php -> codebase-lemberg
            $dir = Str::kebab($system);
            $dirPath = 'reports' . DIRECTORY_SEPARATOR . $dir;

            if (!File::exists($dirPath)) {
                File::makeDirectory($dirPath);
            }

            $stubFile = 'dummy-project-name.csv';
            File::copy(
                base_path('stubs' . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $stubFile),
                base_path($dirPath . DIRECTORY_SEPARATOR . $stubFile)
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

    protected function minutesToHourString($int)
    {
        $hours = floor($int / 60);
        $minutes = $int % 60;

        return $hours . 'h ' . $minutes . 'm';
    }

    protected function getActionInput()
    {
        return trim($this->argument('action'));
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['action', InputArgument::OPTIONAL, 'Available actions: "' . implode('; ', self::$actions) . '"', 'calc'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return
            [
                ['force', 'f', InputOption::VALUE_NONE, 'Force.'],

                [
                    'command',
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'The terminal command that should be assigned.',
                    'command:name'
                ],
            ];
    }
}
