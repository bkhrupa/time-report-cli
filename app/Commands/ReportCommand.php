<?php

namespace App\Commands;

use App\Commands\Report\ReportBase;
use Symfony\Component\Console\Input\InputOption;

class ReportCommand extends ReportBase
{

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
    protected $description = 'Calculate and show current Time Sessions.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->validateConfig();
        $this->prepareReportsPath();

        $this->actionsProcessTimeSessions();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(
            parent::getOptions(),
            [
                ['force', 'f', InputOption::VALUE_NONE, 'Force.'],
            ]
        );
    }
}
