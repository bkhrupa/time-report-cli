<?php

namespace App\Contracts;

use Illuminate\Console\OutputStyle;

interface SystemInterface
{

    /**
     * SystemInterface constructor.
     * @param OutputStyle $consoleOutput
     * @param null $verbosity
     */
    public function __construct(OutputStyle $consoleOutput, $verbosity = null);

    /**
     * @param string $project
     * @param string $ticketId
     * @param int $time
     * @param string $message
     * @return mixed
     */
    public function addTimeToTicket($project, $ticketId, int $time, $message);
}
