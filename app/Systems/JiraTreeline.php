<?php

namespace App\Systems;

use JiraRestApi\Configuration\ArrayConfiguration;
use JiraRestApi\Issue\IssueService;
use JiraRestApi\Issue\Worklog;
use JiraRestApi\JiraException;

class JiraTreeline extends AbstractSystem
{

    /**
     * @inheritdoc
     */
    public function addTimeToTicket($project, $ticketId, int $time, $message)
    {
        try {
            $issueService = new IssueService(new ArrayConfiguration(
                [
                    'jiraHost' => config('jira-treeline.url'),
                    // for basic authorization:
                    'jiraUser' => config('jira-treeline.user'),
                    'jiraPassword' => config('jira-treeline.password'),
                    // to enable session cookie authorization (with basic authorization only)
                    'cookieAuthEnabled' => true,
                    'cookieFile' => config('jira-treeline.cookie_file'),
                ]
            ));

            $workLog = new Worklog();

            $workLog->setComment($message)->setTimeSpent($time . 'm');

            $ret = $issueService->addWorklog($ticketId, $workLog);
            $workLogid = $ret->{'id'};

            $this->consoleOutput->writeln('<fg=black;bg=green>Success. Ticket id: ' . $ticketId . '</>');
        } catch (JiraException $e) {
            dump($e);
        } catch (\Exception $e) {
            dump($e);
        }
    }
}
