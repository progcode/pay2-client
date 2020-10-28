<?php

require_once __DIR__ . '/vendor/autoload.php';

use Pay2\Pay2\Client;
$client = new Client();

$client->testCallback($_REQUEST);
