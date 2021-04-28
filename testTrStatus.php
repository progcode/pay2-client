<?php

require_once __DIR__ . '/vendor/autoload.php';

use Pay2\Pay2\Client;
$client = new Client();

echo "<pre>";
var_dump($client->getPaymentStatus('123456'));
echo "</pre>";
