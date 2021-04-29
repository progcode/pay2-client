<?php

require_once __DIR__ . '/vendor/autoload.php';

use Pay2\Pay2\Client;
$client = new Client();

$orderId = str_replace(array('.', ':', '/'), "", @$_SERVER['SERVER_ADDR']) . @date("U", time()) . rand(1000, 9999);
$payTotal = 34500;

//sample tos
$sampleTos = 'Tu nunc coci ejus. Tu autem cocus Lab et probavimus liceat mihi sine causa est nunc coci interficere.
 Reputo it! Suus egregie. <a href="#">Ut antecedat</a>. Quod si putas me posse facere, ergo ante. Pone aute in caput, et nunc interficere. Faciat! Fac. Fac. Fac.';

//sample customer
$sampleCustomer = [
    'name' => 'Test User',
    'email' => rand().'@pay2.live',
    'phone' => '06301234567',
    'state' => 'Budapest',
    'city' => 'Budapest',
    'zip' => '1234',
    'address' => 'Teszt utca 20',
];

$sampleProduct = [
    'name' => 'Test product',
    'desc' => 'Test product leiras',
    'qty' => '1',
    'sku' => '123456SKU',
];

//build our sample form
$form = $client->buildForm($orderId, $payTotal, $sampleCustomer, $sampleTos, $sampleProduct);
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

    <?php echo $client->getPaymentApiJs(); ?>
    <?php echo $client->getPaymentVendorJs('1234567'); ?>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="card-header">TestForm</div>
        <div class="card-body">
            <?php echo $form; ?>
            <?php echo $client->checkoutMetricsJs('prepare', $orderId); ?>

            <a href="<?php echo $assets->vendor_tos; ?>">
                <img class="img-fluid" src="<?php echo $assets->vendor_logo_with_cards; ?>" />
            </a>

            <p><?php echo $assets->pay2_slogen; ?></p>
            <small>Pay2 version: <?php echo $assets->pay2_version; ?></small>
        </div>
    </div>
</div>

</body>
</html>
