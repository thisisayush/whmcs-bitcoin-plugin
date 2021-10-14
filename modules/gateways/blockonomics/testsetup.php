<?php

require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/blockonomics.php';

use Blockonomics\Blockonomics;

    
$blockonomics = new Blockonomics();

$response = array();
$error = array();

$error = $blockonomics->testSetup();

if (isset($error) && count($error) != 0) {
    if (count(array_filter($error, function($item) { return $item != false; })) != 0) {
        $response = $error;
    }
}

echo json_encode($response);
