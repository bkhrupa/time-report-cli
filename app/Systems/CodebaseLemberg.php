<?php

namespace App\Systems;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\TransferStats;
use Illuminate\Console\OutputStyle;

class CodebaseLemberg extends AbstractSystem
{

    protected $apiUrl;
    protected $domain;
    protected $userLogin;
    protected $apiToken;

    /**
     * {@inheritdoc}
     */
    public function __construct(OutputStyle $consoleOutput, $verbosity = null)
    {
        parent::__construct($consoleOutput, $verbosity);

        $this->apiUrl = config('codebase-lemberg.url');
        $this->domain = config('codebase-lemberg.domain');
        $this->userLogin = config('codebase-lemberg.user');
        $this->apiToken = config('codebase-lemberg.token');
    }

    /**
     * @see https://support.codebasehq.com/kb/tickets-and-milestones/updating-tickets
     *
     * @inheritdoc
     */
    public function addTimeToTicket($project, $ticketId, int $time, $message) : bool
    {
        $apiUrl = $this->apiUrl;
        $user = $this->domain . '/' . $this->userLogin;
        $password = $this->apiToken;

        $client = new Client();

        try {
            $res = $client->request(
                'POST',
                $apiUrl . '/' . $project . '/tickets/' . $ticketId . '/notes',
                [
                    'auth' => [$user, $password],
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                    'json' => [
                        'ticket_note' => [
                            'content' => $message,
                            'time_added' => $time,
                        ]
                    ],
                    'on_stats' => function (TransferStats $stats) use (&$url) {
                        $url = $stats->getEffectiveUri();
                    }
                ]
            );

            if ($res->getStatusCode() == 201) {
                $this->success('Success. Ticket: ' . $ticketId);
                return true;
            } else {
                $this->error((string)$res->getBody());
            }
        } catch (ClientException $e) {
            $this->error($e->getMessage());

            // TODO use verbosity
            if ($this->verbosity) {
                dump($e->getTraceAsString());
            }
//            $this->consoleOutput->error((string)$e->getResponse()->getBody());
        }

        return false;
    }
}
