<?php

require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/blockonomics.php';

use Blockonomics\Blockonomics;

$newApi = filter_var($_GET['new_api'], FILTER_SANITIZE_STRING);

if (isset($newApi)) {
    $blockonomics = new Blockonomics();

    $response = new stdClass();
    $response->error = false;
    $response->errorStr = array();

    $error = $blockonomics->testSetup($newApi);

    if (isset($error) && count($error) != 0) {
        if (count(array_filter($error, function($item) { return $item != false; })) != 0) {
            $response->error = true;
            $response->errorStr = $error;
        }
    }

    echo json_encode($response);
}
