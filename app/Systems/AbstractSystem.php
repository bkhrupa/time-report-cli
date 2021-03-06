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
     * @return bool
     */
    abstract public function addTimeToTicket($project, $ticketId, int $time, $message) : bool;

    public function success($message)
    {
        $this->consoleOutput->writeln('<fg=black;bg=green>' . $message . '</>');
    }

    public function error($message)
    {
        $this->consoleOutput->writeln('<fg=white;bg=red>' . $message . '</>');
    }
}
