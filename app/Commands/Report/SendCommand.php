<?php

namespace App\Commands;

use App\Commands\Report\ReportBase;

class SendCommand extends ReportBase
{

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $name = 'report:send';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Sends all Time Sessions.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->prepareReportsPath();

        $this->actionsProcessTimeSessions(true);
    }
}
