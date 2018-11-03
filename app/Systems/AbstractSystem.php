<?php

namespace App\Systems;

use Illuminate\Console\OutputStyle;

abstract class AbstractSystem
{

    /**
     * @var OutputStyle
     */
    protected $consoleOutput;

    /**
     * @var null
     */
    protected $verbosity;

    /**
     * SystemInterface constructor.
     * @param OutputStyle $consoleOutput
     * @param null $verbosity
     */
    public function __construct(OutputStyle $consoleOutput, $verbosity = null)
    {
        $this->consoleOutput = $consoleOutput;
        $this->verbosity = $verbosity;

    }

    /**
     * @param string $project
     * @param string $ticketId
     * @param int $time
     * @param string $message
     * @return mixed
     */
    abstract public function addTimeToTicket($project, $ticketId, int $time, $message);
}
