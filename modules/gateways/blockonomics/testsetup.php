<?php

require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/blockonomics.php';

use Blockonomics\Blockonomics;
use WHMCS\ClientArea;
use WHMCS\Database\Capsule;

if (isset($_REQUEST['new_api'])) {
    $blockonomics = new Blockonomics();

    $response = new stdClass();
    $response->error = false;

    $error = $blockonomics->testSetup($_REQUEST['new_api']);

    if (isset($error) && $error != '') {
        $response->error = true;
        $response->errorStr = $error;
    }

    $responseJSON = json_encode($response);

    echo $responseJSON;
}
