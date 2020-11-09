<?php

return [
    'url' => env('JIRA_CAREPATHTECH_URL', 'https://atlassian.net'),
    'user' => env('JIRA_CAREPATHTECH_USER', 'foo@gmail.com'),
    'password' => env('JIRA_CAREPATHTECH_PASSWORD', 'secret'),
    'cookie_file' => storage_path('jira-carepathtech-cookie.txt'),
];
