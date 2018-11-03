<?php

return [
    'url' => env('JIRA_TREELINE_URL', 'https://treeline.atlassian.net'),
    'user' => env('JIRA_TREELINE_USER', 'foo@treelineinteractive.com'),
    'password' => env('JIRA_TREELINE_PASSWORD', 'secret'),
    'cookie_file' => storage_path('jira-cookie.txt'),
];
