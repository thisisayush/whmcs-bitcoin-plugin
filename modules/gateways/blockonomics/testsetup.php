<?php

require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/blockonomics.php';

use Blockonomics\Blockonomics;

$newApi = filter_var($_GET['new_api'], FILTER_SANITIZE_STRING);
$currency = filter_var($_GET['currency'], FILTER_SANITIZE_STRING);

if (isset($newApi)) {
    $blockonomics = new Blockonomics();

    $response = new stdClass();
    $response->error = false;
    $response->errorStr = '';

    if(!isset($currency)) {
        $currency = 'btc'
    }

    $error = $blockonomics->testSetup($newApi, $currency);

    if (isset($error) && $error != '') {
        $response->error = true;
        $response->errorStr = $error;
    }

    echo json_encode($response);
}
