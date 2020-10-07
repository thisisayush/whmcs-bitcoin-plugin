<?php
// Checkout pages
$_LANG['blockonomics']['orderId'] = "Order #";
$_LANG['blockonomics']['error']['btc']['title'] = "Could not generate new Bitcoin address.";
$_LANG['blockonomics']['error']['btc']['message'] = "Note to webmaster: Please login to admin and go to Setup > Payments > Payment Gateways > Manage Existing Gateways and use the Test Setup button to diagnose the error.";
$_LANG['blockonomics']['error']['bch']['title'] = "Could not generate new Bitcoin Cash address.";
$_LANG['blockonomics']['error']['bch']['message'] = "Note to webmaster: Please follow the instructions <a href=\"https://help.blockonomics.co/support/solutions/articles/33000251576-bch-setup-on-whmcs\" target=\"_blank\">here</a> to configure BCH payments.";
$_LANG['blockonomics']['error']['pending']['title'] = "Payment is pending";
$_LANG['blockonomics']['error']['pending']['message'] = "Additional payments to invoice are only allowed after current pending transaction is confirmed.";
$_LANG['blockonomics']['payWith'] = "Pay With";
$_LANG['blockonomics']['paymentExpired'] = "Payment Expired";
$_LANG['blockonomics']['tryAgain'] = "Click here to try again";
$_LANG['blockonomics']['paymentError'] = "Payment Error";
$_LANG['blockonomics']['openWallet'] = "Open in wallet";
$_LANG['blockonomics']['payAmount'] = "To pay, send exactly this [[currency.code | uppercase]] amount";
$_LANG['blockonomics']['payAddress'] = "To this [[currency.name | lowercase]] address";
$_LANG['blockonomics']['copyClipboard'] = "Copied to clipboard";
$_LANG['blockonomics']['howToPay'] = "How do I pay?";
$_LANG['blockonomics']['poweredBy'] = "Powered by Blockonomics";

// Callback
$_LANG['blockonomics']['error']['secret'] = "Secret verification failure";
$_LANG['blockonomics']['invoiceNote']['waiting'] = "Waiting for Confirmation on";
$_LANG['blockonomics']['invoiceNote']['network'] = "network";

// Admin Menu
$_LANG['blockonomics']['version']['title'] = "Version";
$_LANG['blockonomics']['apiKey']['title'] = "API Key";
$_LANG['blockonomics']['apiKey']['description'] = "BLOCKONOMICS API KEY (Click \"Get Started For Free\" on <a target=\"_blank\" href=\"https://www.blockonomics.co/blockonomics#/merchants\">Merchants</a> and follow setup wizard)";
$_LANG['blockonomics']['enabled']['title'] = "Enabled";
$_LANG['blockonomics']['enabled']['description'] = "Select if you want to accept";
$_LANG['blockonomics']['callbackSecret']['title'] = "Callback Secret";
$_LANG['blockonomics']['callbackUrl']['title'] = "Callback URL";
$_LANG['blockonomics']['timePeriod']['title'] = "Time Period";
$_LANG['blockonomics']['timePeriod']['description'] = "Time period of countdown timer on payment page (in minutes)";
$_LANG['blockonomics']['margin']['title'] = "Extra Currency Rate Margin %";
$_LANG['blockonomics']['margin']['description'] = "Increase live fiat to BTC rate by small percent";
$_LANG['blockonomics']['slack']['title'] = "Underpayment Slack %";
$_LANG['blockonomics']['slack']['description'] = "Allow payments that are off by a small percentage";
$_LANG['blockonomics']['confirmations']['title'] = "Confirmations";
$_LANG['blockonomics']['confirmations']['description'] = "Network Confirmations required for payment to complete";
$_LANG['blockonomics']['confirmations']['recommended'] = "recommended";

// Test Setup
$_LANG['blockonomics']['testSetup']['systemUrl']['error'] = "Unable to locate/execute";
$_LANG['blockonomics']['testSetup']['systemUrl']['fix'] = "Check your WHMCS System URL. Go to Setup > General Settings and verify your WHMCS System URL";
$_LANG['blockonomics']['testSetup']['success'] = "Congrats! Setup is all done";
$_LANG['blockonomics']['testSetup']['protocol']['error'] = "Error: System URL has a different protocol than current URL.";
$_LANG['blockonomics']['testSetup']['protocol']['fix'] = "Go to Setup > General Settings and verify that WHMCS System URL has correct protocol set (HTTP or HTTPS).";
$_LANG['blockonomics']['testSetup']['testing'] = "Testing setup...";
$_LANG['blockonomics']['testSetup']['newApi'] = "New API Key: Save your changes and then click 'Test Setup'";
$_LANG['blockonomics']['testSetup']['blockedHttps'] = "Your server is blocking outgoing HTTPS calls";
$_LANG['blockonomics']['testSetup']['incorrectApi'] = "API Key is incorrect";
$_LANG['blockonomics']['testSetup']['noXpub'] = "You have not entered an xpub";
$_LANG['blockonomics']['testSetup']['existingCallbackUrl'] = "Your have an existing callback URL. Refer instructions on integrating multiple websites";
$_LANG['blockonomics']['testSetup']['multipleXpubs'] = "Your have an existing callback URL or multiple xPubs. Refer instructions on integrating multiple websites";
