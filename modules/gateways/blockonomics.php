<?php

require_once(dirname(__FILE__) . '/Blockonomics/Blockonomics.php');

use Blockonomics\Blockonomics;

function blockonomics_config() {

	// When loading plugin setup page, run custom JS
	add_hook('AdminAreaFooterOutput', 1, function($vars) {
		try {
		    // Detect module name from filename.
			$gatewayModuleName = basename(__FILE__, '.php');
			// Fetch gateway configuration parameters.
			$gatewayParams = getGatewayVariables($gatewayModuleName);
		}
		catch (exception $e) {
		    return;
		}
		$blockonomics = new Blockonomics();
		$system_url = $blockonomics->getSystemUrl();
		$callback_url = $blockonomics->getCallbackUrl();

		return <<<HTML
		<script type="text/javascript">
			/**
			 * Disable callback url editing
			 */
			var inputFields = document.getElementsByName('field[CallbackURL]');
			inputFields.forEach(function(element) {
				element.value = '$callback_url';
				element.readOnly = true;
			});

			/**
			 * Padding for config labels
			 */
			var inputLabels = document.getElementsByClassName('fieldlabel');

			for(var i = 0; i < inputLabels.length; i++) {
				inputLabels[i].style.paddingRight = '20px';
			}

			/**
			 * Set available values for margin setting
			 */
			var inputMargin = document.getElementsByName('field[Margin]');
			inputMargin.forEach(function(element) {
				element.type = 'number';
				element.min = 0;
				element.max = 4;
				element.step = 0.01;
			});
			var inputSlack = document.getElementsByName('field[Slack]');
			inputSlack.forEach(function(element) {
				element.type = 'number';
				element.min = 0;
				element.max = 10;
				element.step = 0.01;
			});

			/**
			 * Generate Test Setup button and setup result field
			 */
			var settingsTable = document.getElementById("Payment-Gateway-Config-blockonomics");

			var testSetupBtnRow = settingsTable.insertRow(settingsTable.rows.length - 1);
			var testSetupLabelCell = testSetupBtnRow.insertCell(0);
			var testSetupBtnCell = testSetupBtnRow.insertCell(1);
			testSetupBtnCell.className = "fieldarea";

			var testSetupResultRow = settingsTable.insertRow(settingsTable.rows.length - 1);
			testSetupResultRow.style.display = "none";
			var testSetupResultLabel = testSetupResultRow.insertCell(0);
			var testSetupResultCell = testSetupResultRow.insertCell(1);
			testSetupResultCell.className = "fieldarea";

			var newBtn = document.createElement('BUTTON');
			newBtn.className = "btn btn-primary";

			var t = document.createTextNode("Test Setup");
			newBtn.appendChild(t);

			testSetupBtnCell.appendChild(newBtn);

			function reqListener () {
				var responseObj = {};
				try {
					responseObj = JSON.parse(this.responseText);
				} catch (err) {
					var testSetupUrl = "$system_url" + "testSetup.php";
					responseObj.error = true;
					responseObj.errorStr = 'Unable to locate/execute ' + testSetupUrl + '. Check your WHMCS System URL ';
				}
				if (responseObj.error) {
					testSetupResultCell.innerHTML = "<label style='color:red;'>Error:</label> " + responseObj.errorStr + 
					"<br>For more information, please consult <a href='https://blockonomics.freshdesk.com/support/solutions/articles/33000215104-troubleshooting-unable-to-generate-new-address' target='_blank'>this troubleshooting article</a>";
				} else {
					testSetupResultCell.innerHTML = "<label style='color:green;'>Congrats! Setup is all done</label>";
				}
				newBtn.disabled = false;
			}

			newBtn.onclick = function() {
				testSetupResultRow.style.display = "table-row";
				var apiKeyField = document.getElementsByName('field[ApiKey]')[0];
				var testSetupUrl = "$system_url" + "testSetup.php"+"?new_api="+apiKeyField.value;

				try {
					var systemUrlProtocol = new URL("$system_url").protocol;
				} catch (err) {
					var systemUrlProtocol = '';
				}

				if (systemUrlProtocol != location.protocol) {
					testSetupResultCell.innerHTML = "<label style='color:red;'>Error:</label> \
							System URL has a different protocol than current URL. Go to Setup > General Settings and verify that WHMCS System URL has \
							correct protocol set (HTTP or HTTPS).";
					return false;
				}
				
				var oReq = new XMLHttpRequest();
				oReq.addEventListener("load", reqListener);
				oReq.open("GET", testSetupUrl);
				oReq.send();

				newBtn.disabled = true;
				testSetupResultCell.innerHTML = "Testing setup...";

				return false;
			}

		</script>
HTML;

	});

	$blockonomics = new Blockonomics();
	$blockonomics->createOrderTableIfNotExist();
	
	$settings_array = array(
		'FriendlyName' => array(
			'Type'       => 'System',
			'Value'      => 'Blockonomics'
		),
		'GetStarted' => array(
			'FriendlyName' => '<b>Blockonomics API Key\'s</b>',
			'Description'  => '<a target="_blank" href="https://www.blockonomics.co/merchants#/" class="btn btn-primary get-api">Get API Key</a> Click <i>Get Started For Free</i> and follow the setup wizard',
			'Type'         => 'none'
		)
	);

	$blockonomics_currencies = json_decode($blockonomics->getSupportedCurrencies());
	foreach ($blockonomics_currencies->currencies as $currency) {
		if($currency->code == 'btc'){
			$settings_array['ApiKey'] = array(
					'FriendlyName' => '<img src="../img/'.$currency->code.'.png" alt="'.$currency->name.' Logo"> '.$currency->name.' API Key',
					'Type'         => 'text'
				);
		}else{
			$settings_array[ $currency->code.'ApiKey' ] = array(
					'FriendlyName' => '<img src="../img/'.$currency->code.'.png" alt="'.$currency->name.' Logo"> '.$currency->name.' API Key',
					'Type'         => 'text',
					'Placeholder'  => 'Optional'
				);
		}
	}
	$settings_array[ 'CallbackURL' ] = array(
			'FriendlyName' => 'Callback URL',
			'Description'  => 'Copy this url and set in <a target="_blank" href="https://www.blockonomics.co/merchants#/page3">Merchants</a>',
			'Type'         => 'text'
		);
	$settings_array[ 'TimePeriod' ] = array(
			'FriendlyName' => 'Time Period',
			'Type' => 'dropdown',
			'Options' => array(
				'10' => '10',
				'15' => '15',
				'20' => '20',
				'25' => '25',
				'30' => '30',
			),
			'Description' => 'Time period of countdown timer on payment page (in minutes)',
		);
	$settings_array[ 'Margin' ] = array(
				'FriendlyName' => 'Extra Currency Rate Margin %',
				'Type' => 'text',
				'Size' => '5',
				'Default' => 0,
				'Description' => 'Increase live fiat to BTC rate by small percent',
		);
	$settings_array[ 'Slack' ] = array(
				'FriendlyName' => 'Underpayment Slack %',
				'Type' => 'text',
				'Size' => '5',
				'Default' => 0,
				'Description' => 'Allow payments that are off by a small percentage',
		);
	$settings_array[ 'Confirmations' ] = array(
			'FriendlyName' => 'Confirmations',
			'Type' => 'dropdown',
			'Default' => 2,
			'Options' => array(
				'2' => '2 (recommended)',
				'1' => '1',
				'0' => '0'
			),
			'Description' => 'Network Confirmations required for payment to complete',
		);
	
	return $settings_array;
}

function blockonomics_link($params) {
	
	if (false === isset($params) || true === empty($params)) {
		die('[ERROR] In modules/gateways/Blockonomics.php::Blockonomics_link() function: Missing or invalid $params data.');
	}

	$blockonomics = new Blockonomics();
	$order_uuid = $blockonomics->newOrder($params['amount'], $params['currency'], $params['invoiceid']);
	
	$system_url = $blockonomics->getSystemUrl();
	$form_url = $system_url . 'payment.php';

	//pass only the uuid to the payment page
	$form = '<form action="' . $form_url . '" method="GET">';
	$form .= '<input type="hidden" name="order" value="'. $order_uuid .'"/>';
	$form .= '<input type="submit" value="'. $params['langpaynow'] .'"/>';
	$form .= '</form>';
	
	return $form;
}
