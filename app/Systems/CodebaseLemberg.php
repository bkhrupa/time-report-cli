<?php

namespace App\Systems;
use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;

class CodebaseLemberg
{
    public function __construct($apiUrl, $domain, $userLogin, $apiToken)
    {
        $this->apiUrl = $apiUrl;
        $this->domain = $domain;
        $this->userLogin = $userLogin;
        $this->apiToken = $apiToken;
    }

    /**
     * @see https://support.codebasehq.com/kb/tickets-and-milestones/updating-tickets
     *
     * @param $project
     * @param $ticketId
     * @param $time
     * @param string $message
     *
     *
     */
    public function addTimeToTicket($project, $ticketId, $time, $message = '')
    {
        $apiUrl = env('CODEBASE_API_URL');
        $user = $this->domain . '/' . $this->userLogin;
        $password = $this->apiToken;

        $client = new Client();
        $res = $client->request(
            'POST',
            $this->apiUrl . '/'.$project.'/tickets/'.$ticketId.'/notes',
            [
                'auth' => [$user, $password],
                'query' => [
                    'from' => date('Y-m-29')
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'on_stats' => function (TransferStats $stats) use (&$url) {
                    $url = $stats->getEffectiveUri();
                }
            ]
        );

        dump($res->getStatusCode());
        dump($url);

        dump(json_decode($res->getBody()));
    }
}
