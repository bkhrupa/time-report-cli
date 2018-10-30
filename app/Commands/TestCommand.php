<?php

namespace App\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\TransferStats;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;

class TestCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'test:command';

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

//        $this->line(ucfirst(Str::camel('codebase-lemberg')));
//        return;

        $apiUrl = env('CODEBASE_API_URL');
        $user = env('CODEBASE_API_DOMAIN') . '/' . env('CODEBASE_API_USER');
        $password = env('CODEBASE_API_USER_API_TOKEN');


        $client = new Client();
        $res = $client->request(
            'GET',
            $apiUrl . '/lemberg-hub/time_sessions',
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

        dump((string)$url);

        dump($res->getStatusCode());
// "200"
//        dump($res->getHeaders());
// 'application/json; charset=utf8'
        dump(json_decode($res->getBody()));

//        // Update ticket
//        $project = 'lemberg-hub';
//        $ticketId = 2;
//
//        $client = new Client();
//
//        try {
//            $res = $client->request(
//                'POST',
//                $apiUrl . '/' . $project . '/tickets/' . $ticketId . '/notes',
//                [
//                    'auth' => [$user, $password],
//                    'headers' => [
//                        'Content-Type' => 'application/json',
//                        'Accept' => 'application/json',
//                    ],
//                    'json' => [
//                        'ticket_note' => [
//                            'content' => 'asd',
//                        ]
//                    ],
//                    'on_stats' => function (TransferStats $stats) use (&$url) {
//                        $url = $stats->getEffectiveUri();
//                    }
//                ]
//            );
//
//
//            dump($res->getStatusCode());
//            dump($url);
//
//            dump(json_decode($res->getBody()));
//
//        }
//        catch (ClientException $e) {
//            $this->error($e->getMessage());
//            dump(get_class($e));
//            dump((string)$e->getResponse()->getBody());
//        }


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
}
