<?php

namespace App\Commands;

use App\Systems\AbstractSystem;
use Illuminate\Support\Collection;
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
     * @var string
     */
    protected $reportsPath = '';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->validateConfig();
        $this->prepareReportsPath();

        $action = $this->getActionInput();

        switch ($action) {
            case self::ACTION_SEND:
                $this->actionsProcessTimeSessions(true);
                break;
            case self::ACTION_MAKE_STUBS:
                $this->actionMakeStubs();
                break;
            case self::ACTION_CALC:
            default:
                $this->actionsProcessTimeSessions();
                break;
        }
    }

    protected function actionsProcessTimeSessions(bool $isNeedToSend = false)
    {
        $totalMinutes = 0;
        $dataCollection = $this->getReportsData();

        foreach ($dataCollection as $report) {
            $report = new Collection($report);
            $rows = new Collection($report->get('rows', []));
            $projectTotalMinutes = 0;

            // skip empty reports
            if (!$rows->sum('minutes')) {
                continue;
            }

            $this->line('');
            $this->line('Project: <comment>' . $report->get('project') . '</comment> ' .
                'Tickets: <comment>' . $rows->count() . '</comment> ' .
                '(<bg=default;fg=blue>' . $report->get('system') . '</>) '
            );

            if (!$isNeedToSend) {
                foreach ($rows as $row) {
                    $minutes = $row['minutes'];
                    $ticket = $row['ticket'];
                    $message = $row['message'];

                    // Must be 15 if divided
                    // TODO add to send process
                    if ($minutes % 15) {
                        $this->error($minutes . 'm' . "\t" . $this->minutesToHourString($minutes) . "\t" . $ticket . "\t" . $message);
                    } else {
                        $this->line($minutes . 'm' . "\t" . $this->minutesToHourString($minutes) . "\t" . $ticket . "\t" . $message);
                    }


                    $totalMinutes = $totalMinutes + $minutes;
                    $projectTotalMinutes = $projectTotalMinutes + $minutes;
                }

                $this->line('');
                // Must be 15 if divided
                if ($projectTotalMinutes % 15) {
                    $this->error('Total by <comment>"' . $report->get('project') . '"</comment>: ' . $this->minutesToHourString($projectTotalMinutes));
                } else {
                    $this->info('Total by <comment>"' . $report->get('project') . '"</comment>: ' . $this->minutesToHourString($projectTotalMinutes));
                }
            }

            if ($isNeedToSend) {
                $systemClassName = 'App\\Systems\\' . $report->get('system');

                if (!class_exists($systemClassName)) {
                    $this->error('Class ' . $systemClassName . ' not found');
                    continue;
                }

                /** @var AbstractSystem $systemInstance */
                $systemInstance = new $systemClassName($this->getOutput());

                $rowsToReset = new Collection();

                foreach ($rows as $row) {
                    $minutes = $row['minutes'];
                    $ticket = $row['ticket'];
                    $message = $row['message'];

                    $this->line('Starting send: <comment>' . $ticket . '</comment>');

                    if ($minutes < 1) {
                        $this->warn('Skipp. 0 minutes in ticket: ' . $ticket);

                        // Add to reset
                        $rowsToReset->push($row);

                        continue;
                    }

                    $isSuccess = $systemInstance->addTimeToTicket(
                        $report->get('project'),
                        $ticket,
                        $minutes,
                        $message
                    );

                    $totalMinutes = $totalMinutes + $minutes;

                    // reset minutes for success sending ticket
                    if ($isSuccess) {
                        $row['minutes'] = 0;
                    }
                    // Add to reset
                    $rowsToReset->push($row);
                }

                // reset minutes
                $this->line('');
                $this->line('Resetting data for <comment>' . $report->get('project') . '</comment>');

                $fileHandle = fopen($report->get('full_path'), 'w');

                $rowsToReset->each(function ($row) use ($fileHandle) {
                    fputcsv($fileHandle, $row, ';', '\'');
                });

                fclose($fileHandle);
            }
        };

        $this->line('');
        $this->alert('Total: ' . $this->minutesToHourString($totalMinutes) . ' (' . $totalMinutes . ') minutes');
    }

    /**
     *
     */
    public function actionMakeStubs()
    {
        foreach (File::allFiles(app_path('Systems')) as $file) {
            $system = str_replace('.php', '', $file->getFilename());

            if ($system == 'AbstractSystem') {
                continue;
            }

            // Systems/CodebaseLemberg.php -> codebase-lemberg
            $dir = Str::kebab($system);
            $dirPath = $this->reportsPath . DIRECTORY_SEPARATOR . $dir;

            if (!File::exists($dirPath)) {
                File::makeDirectory($dirPath);
            }

            $stubFile = 'dummy-project-name.csv';
            $stubContent = '0;3;\'This is a stub example CSV file\'' . PHP_EOL;
            File::put($dirPath . DIRECTORY_SEPARATOR . $stubFile, $stubContent);

            $this->info('Stub CSV file created for system "' . $system . '"');
        }
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    protected function getReportsData()
    {
        $data = [];

        foreach (File::allFiles($this->reportsPath) as $file) {
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
                $row = fgetcsv($fileHandle, 0, ';', '\'');

                if ($row) {
                    if (count($row) !== 3) {
                        $this->error('Syntactic error in line:');
                        $this->line(implode(';', $row));
                        exit;
                    }

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

        return new Collection($data);
    }

    /**
     * @param $int
     * @return string
     */
    protected function minutesToHourString(int $int)
    {
        $hours = floor($int / 60);
        $minutes = $int % 60;

        return $hours . 'h ' . $minutes . 'm';
    }

    /**
     * @return string
     */
    protected function getActionInput()
    {
        return trim($this->argument('action'));
    }

    /**
     * @return void
     */
    protected function prepareReportsPath()
    {
        $this->reportsPath = $this->option('reports-path');

        if (!File::exists($this->reportsPath)) {
            $this->error('Reports path not exists. "' . $this->reportsPath . '"');
            exit;
        }
    }

    protected function validateConfig()
    {
        // TODO implements
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'action',
                InputArgument::OPTIONAL,
                'Available actions: "' . implode('; ', self::$actions) . '"' .
                PHP_EOL .
                '"calc" - calculate time sum' .
                PHP_EOL .
                '"send" - send all time to systems by projects' .
                PHP_EOL .
                '"make-stubs" - create default CSV files by systems' .
                PHP_EOL
                ,
                'calc'
            ],
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
                ['reports-path', null, InputOption::VALUE_OPTIONAL, 'Reports path', 'reports'],
                ['force', 'f', InputOption::VALUE_NONE, 'Force.'],
            ];
    }
}
