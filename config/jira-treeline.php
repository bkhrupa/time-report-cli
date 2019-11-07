<?php

return [
    'url' => env('JIRA_TREELINE_URL', 'https://atlassian.net'),
    'user' => env('JIRA_TREELINE_USER', 'foo@gmail.com'),
    'password' => env('JIRA_TREELINE_PASSWORD', 'secret'),
    'cookie_file' => storage_path('jira-treeline-cookie.txt'),
];
