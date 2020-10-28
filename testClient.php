<?php

require_once __DIR__ . '/vendor/autoload.php';

use Pay2\Pay2\Client;
$client = new Client();

$orderId = str_replace(array('.', ':', '/'), "", @$_SERVER['SERVER_ADDR']) . @date("U", time()) . rand(1000, 9999);
$payTotal = 345000;

//sample tos
$sampleTos = 'Tu nunc coci ejus. Tu autem cocus Lab et probavimus liceat mihi sine causa est nunc coci interficere.
 Reputo it! Suus egregie. <a href="#">Ut antecedat</a>. Quod si putas me posse facere, ergo ante. Pone aute in caput, et nunc interficere. Faciat! Fac. Fac. Fac.';

//sample customer
$sampleCustomer = [
    'name' => 'Test User',
    'email' => rand().'@pay2.live',
    'city' => 'Budapest',
    'zip' => '1234',
    'address' => 'Teszt utca 20',
];

//build our sample form
$form = $client->buildForm($orderId, $payTotal, $sampleCustomer, $sampleTos);
$assets = $client->getPaymentAssets();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pay2Client Test</title>

    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.0/css/bootstrap.min.css" media="all" />

    <style>
        body {
            padding-top: 30px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="card-header">TestForm</div>
        <div class="card-body">
            <?php echo $form; ?>

            <a href="<?php echo $assets->otpsimple_tos; ?>">
                <img class="img-fluid" src="<?php echo $assets->otpsimple_logo_with_cards; ?>" />
            </a>

            <p><?php echo $assets->pay2_slogen; ?></p>
            <small>Pay2 version: <?php echo $assets->pay2_version; ?></small>
        </div>
    </div>
</div>

</body>
</html>
