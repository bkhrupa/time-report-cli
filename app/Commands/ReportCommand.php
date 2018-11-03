<?php

namespace App\Commands;

use App\Systems\SystemInterface;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use Maatwebsite\Excel\Facades\Excel;
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
        $action = $this->getActionInput('action');

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

        $data = [];
        foreach (File::allFiles(base_path('reports')) as $file) {
            $system = explode(DIRECTORY_SEPARATOR, $file->getRelativePathname())[0];
            $project = str_replace('.csv', '', $file->getFilename());
            $rows = [];

            $data[$file->getFilename()] = [
                'full_path' => $file->getPathname(),
                'system' => ucfirst(Str::camel($system)),
                'project' => $project,
                'rows' => []
            ];

            $fileHandle = fopen($file, 'r');

            while (!feof($fileHandle)) {
                $row = fgetcsv($fileHandle, 0, ';');

                if ($row) {
                    array_push($rows, [
                        'minutes' => (integer)trim($row[0]),
                        'ticket' => trim($row[1]),
                        'message' => trim($row[2]),
                    ]);
                }
            }

            fclose($fileHandle);
            $data[$file->getFilename()]['rows'] = $rows;
        }

        //
        $dataCollection = new Collection($data);

        foreach ($dataCollection as $report) {
            $report = new Collection($report);
            $rows = new Collection($report->get('rows', []));
            $projectTotalMinutes = 0;

            $this->line('');
            $this->line('Project: <comment>' . $report->get('project') . '</comment> (<bg=default;fg=blue>' . $report->get('system') . '</>)');

            if (!$isNeedToSend) {
                foreach ($rows as $row) {
                    $minutes = $row['minutes'];
                    $ticket = $row['ticket'];
                    $message = $row['message'];

                    $this->line($minutes . "m\t" . $ticket . "\t" . $message);

                    $totalMinutes = $totalMinutes + $minutes;
                    $projectTotalMinutes = $projectTotalMinutes + $minutes;
                }

                $this->line('');
                $this->info('Total by "' . $report->get('project') . '": ' . $this->minutesToHourString($projectTotalMinutes));
            }

            if ($isNeedToSend) {
                $this->info('Starting send for project ' . $report->get('project') . '. Tickets count ' . $rows->count());

                $systemClassName = 'App\\Systems\\' . $report->get('system');

                if (!class_exists($systemClassName)) {
                    $this->error('Class ' . $systemClassName . ' not found');
                    continue;
                }

                /** @var SystemInterface $systemInstance */
                $systemInstance = new $systemClassName($this->getOutput());

                foreach ($rows as $row) {
                    $minutes = $row['minutes'];
                    $ticket = $row['ticket'];
                    $message = $row['message'];

                    $this->line('sending ' . $ticket);

                    if ($minutes < 1) {
                        $this->warn('Skipping. 0 minutes in ticket: ' . $ticket);
                        continue;
                    }

                    $systemInstance->addTimeToTicket(
                        $report->get('project'),
                        $ticket,
                        $minutes,
                        $message
                    );

                    $totalMinutes = $totalMinutes + $minutes;
                }

                // reset minutes
                $this->info('Resetting data for' . $report->get('project'));

                $rows = $rows->map(function ($item) {
                    $item['minutes'] = 0;
                    return $item;
                });

                $fileHandle = fopen($report->get('full_path'), 'w');

                $rows->each(function ($row) use ($fileHandle) {
                    fputcsv($fileHandle, $row, ';');
                });

                fclose($fileHandle);
            }
        };

        $this->line('');
        $this->alert('Total: ' . $this->minutesToHourString($totalMinutes) . ' (' . $totalMinutes . ') minutes');

    }

    public function makeStubs()
    {
        foreach (File::allFiles(app_path('Systems')) as $file) {
            $system = str_replace('.php', '', $file->getFilename());

            if ($system == 'AbstractSystem') {
                continue;
            }

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
