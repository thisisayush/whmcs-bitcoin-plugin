<?php

namespace Blockonomics;

use WHMCS\Database\Capsule;

class Blockonomics {

	/*
	 * Get callback secret and SystemURL to form the callback URL
	 */
	public function getCallbackUrl() {
		$secret = $this->getCallbackSecret();
		$callback_url = $this->getSystemUrl() . 'modules/gateways/callback/blockonomics.php?secret=' . $secret;
		return $callback_url;
	}

	/*
	 * Try to get callback secret from db
	 * If no secret exists, create new
	 */
	public function getCallbackSecret() {

		$secret = '';

		try {
			$secret = Capsule::table('tblpaymentgateways')
					->where('gateway', 'blockonomics')
					->where('setting', 'CallbackSecret')
					->value('value');

		} catch(\Exception $e) {
			exit("Error, could not get Blockonomics secret from database. {$e->getMessage()}");
		}

		// Check if old format of callback is still in use
		if($secret == '') {
			try {
				$secret = Capsule::table('tblpaymentgateways')
						->where('gateway', 'blockonomics')
						->where('setting', 'ApiSecret')
						->value('value');

			} catch(\Exception $e) {
				exit("Error, could not get Blockonomics secret from database. {$e->getMessage()}");
			}
			// Get only the secret from the whole Callback URL
			$secret = substr($secret, -40);
		}
			
		if($secret == '') {
			$secret = $this->generateCallbackSecret();
		}

		return $secret;
	}

	/*
	 * Generate new callback secret using sha1, save it in db under tblpaymentgateways table
	 */
	private function generateCallbackSecret() {

		try {
			$callback_secret = sha1(openssl_random_pseudo_bytes(20));

			$secret = Capsule::table('tblpaymentgateways')->insert([
				['gateway' => 'blockonomics', 'setting' => 'CallbackSecret', 'value' => $callback_secret]
			]);

		} catch(\Exception $e) {
			exit("Error, could not get Blockonomics secret from database. {$e->getMessage()}");
		}

		return $callback_secret;
	}

	/*
	 * Get user configured API key from database
	 */
	public function getApiKey() {
		return Capsule::table('tblpaymentgateways')
				->where('gateway', 'blockonomics')
				->where('setting', 'ApiKey')
				->value('value');
	}

	/*
	 * Get list of crypto currencies supported by Blockonomics
	 */
	public function getSupportedCurrencies() {
		$all_currencies = array(
  			'btc' => array(
			        'name' => 'Bitcoin',
			        'uri' => 'bitcoin'
  			),
  			'bch' => array(
			        'name' => 'Bitcoin Cash',
			        'uri' => 'bitcoincash'
  			)
  		);
		return $all_currencies;
	}

	/*
	 * Get list of active crypto currencies
	 */
	public function getActiveCurrencies() {
		$active_currencies = array();
		$blockonomics_currencies = $this->getSupportedCurrencies();
		foreach ($blockonomics_currencies as $code => $currency) {
			if($code == 'btc'){
				$enabled = true;
			}else{
				$enabled = Capsule::table('tblpaymentgateways')
					->where('gateway', 'blockonomics')
					->where('setting', $code.'Enabled')
					->value('value');
			}
			if($enabled){
				$active_currencies[$code] = $currency;
			}
		}
		return $active_currencies;
	}

	/*
	 * Get user configured Time Period from database
	 */
	public function getTimePeriod() {
		return Capsule::table('tblpaymentgateways')
			->where('gateway', 'blockonomics')
			->where('setting', 'TimePeriod')
			->value('value');
	}

	/*
	 * Get user configured Confirmations from database
	 */
	public function getConfirmations() {
		$confirmations = Capsule::table('tblpaymentgateways')
			->where('gateway', 'blockonomics')
			->where('setting', 'Confirmations')
			->value('value');
		if(isset($confirmations)){
			return $confirmations;
		}
		return 2;
	}

	/*
	 * Update order status to 'Waiting for Bitcoin Confirmation'
	 */
	public function updateOrderStatus($orderId, $status) {
		Capsule::table('tblorders')
			->where('id', $orderId)
			->update(['status' => $status]);
	}

	/*
	 * Update invoice status
	 */
	public function updateInvoiceStatus($invoiceId, $status) {
		Capsule::table('tblinvoices')
			->where('id', $invoiceId)
			->update(['status' => $status]);
	}

	/*
	 * Update order note
	 */
	public function updateOrderNote($orderId, $note) {
		Capsule::table('tblorders')
			->where('id', $orderId)
			->update(['notes' => $note]);
	}

	/*
	 * Update invoice note
	 */
	public function updateInvoiceNote($invoiceid, $note) {
		Capsule::table('tblinvoices')
			->where('id', $invoiceid)
			->update(['notes' => $note]);
	}

	/*
	 * Get order id by invoice id
	 */
	public function getOrderIdByInvoiceId($invoiceId) {
		return Capsule::table('tblorders')
			->where('invoiceid', $invoiceId)
			->value('id');
	}

	/*
	 * Get the BTC price that was calculated when the order price was last updated
	 */
	public function getPriceByExpected($invoiceId) {
		$query = Capsule::table('blockonomics_bitcoin_orders')
			->where('id_order', $invoiceId)
			->select('value');
		$prices = $query->addSelect('bits')->get();
		$fiat = $prices[0]->value;
		$btc = $prices[0]->bits / 1.0e8;
		$btc_price = $fiat / $btc;
		return round($btc_price, 2);
	}

	/*
	 * Get underpayment slack
	 */
	public function getUnderpaymentSlack() {
		return Capsule::table('tblpaymentgateways')
			->where('gateway', 'blockonomics')
			->where('setting', 'Slack')
			->value('value');
	}

	/*
	 * Get new address from Blockonomics Api
	 */
	public function getNewAddress($currency='btc', $reset=false) {
		if($currency=='btc'){
			$subdomain = 'www';
		}else{
			$subdomain = $currency;
		}

		$api_key = $this->getApiKey();
		$callback_secret = $this->getCallbackSecret();

		if($reset) {
				$get_params = "?match_callback=$callback_secret&reset=1";
		} 
		else {
				$get_params = "?match_callback=$callback_secret";
		}

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, "https://".$subdomain.".blockonomics.co/api/new_address" . $get_params);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");

		$header = "Authorization: Bearer " . $api_key;
		$headers = array();
		$headers[] = $header;
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$contents = curl_exec($ch);
		if (curl_errno($ch)) {
				exit('Error:' . curl_error($ch));
		}

		$responseObj = json_decode($contents);
		//Create response object if it does not exist
		if (!isset($responseObj)) $responseObj = new \stdClass();
		$responseObj->{'response_code'} = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close ($ch);
		return $responseObj;
	}

	/*
	 * Get user configured margin from database
	 */
	public function getMargin() {
		return Capsule::table('tblpaymentgateways')
			->where('gateway', 'blockonomics')
			->where('setting', 'Margin')
			->value('value');
	}

	/*
	 * Convert fiat amount to Blockonomics currency
	 */
	public function convertFiatToBlockonomicsCurrency($fiat_amount , $blockonomics_currency = 'btc') {
		$currency = getCurrency(getClientsDetails()['user_id'])['code'];
		try {
			if($blockonomics_currency=='btc'){
				$subdomain = 'www';
			}else{
				$subdomain = $blockonomics_currency;
			}

			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, "https://".$subdomain.".blockonomics.co/api/price?currency=".$currency);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

			$contents = curl_exec($ch);
			if (curl_errno($ch)) {
					exit('Error:' . curl_error($ch));
			}
			curl_close ($ch);
			$price = json_decode($contents)->price;
			$margin = floatval($this->getMargin());
			if($margin > 0){
				$price = $price * 100/(100+$margin);
			}
		} catch (\Exception $e) {
			exit("Error getting price from Blockonomics! {$e->getMessage()}");
		}

		return intval(1.0e8 * $fiat_amount/$price);
	}

	/*
	 * If no Blockonomics order table exists, create it
	 */
	public function createOrderTableIfNotExist() {

		if (!Capsule::schema()->hasTable('blockonomics_bitcoin_orders')) {

			try {
				Capsule::schema()->create( 'blockonomics_bitcoin_orders', function ($table) {
							$table->increments('id');
							$table->integer('id_order');
							$table->text('txid');
							$table->integer('timestamp');
							$table->text('addr');
							$table->integer('status');
							$table->float('value');
							$table->integer('bits');
							$table->integer('bits_payed');
							$table->string('blockonomics_currency');
						}
				);
			} catch (\Exception $e) {
					exit("Unable to create blockonomics_bitcoin_orders: {$e->getMessage()}");
			}
		}else{
			if(!Capsule::schema()->hasColumn('blockonomics_bitcoin_orders', 'blockonomics_currency')){
				 Capsule::schema()->table('blockonomics_bitcoin_orders', function($table){
					$table->string('blockonomics_currency');
					// Add btc as the blockonomics_currency for existing orders
					try {
						Capsule::table('blockonomics_bitcoin_orders')
								->where('blockonomics_currency', '')
								->update([
									'blockonomics_currency' => 'btc'
								]
							);
					} catch (\Exception $e) {
						exit("Unable to set default value for existing blockonomics_bitcoin_orders: {$e->getMessage()}");
					}
				 });
			}
			if(Capsule::schema()->hasColumn('blockonomics_bitcoin_orders', 'flyp_id')){
			    Capsule::schema()->table('blockonomics_bitcoin_orders', function($table){
			        $table->dropColumn('flyp_id');
			    });
			}
		}
	}

    /**
     * Decrypts a string using the application secret.
     */
    public function decrypt(string $input): string
    {
    	$encryption_algorithm = 'AES-128-CBC';
    	$hashing_algorith = 'sha256';
    	$secret = $this->getCallbackSecret();
        // prevent decrypt failing when $input is not hex or has odd length
        if (strlen($input) % 2 || ! ctype_xdigit($input)) {
            return '';
        }

        // we'll need the binary cipher
        $binaryInput = hex2bin($input);
        $iv = substr($secret, 0, 16);
        $cipherText = $binaryInput;
        $key = hash($hashing_algorith, $secret, true);

        return openssl_decrypt(
            $cipherText,
            $encryption_algorithm,
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );
    }

    /**
     * Encrypts a string using the application secret. This returns a hex representation of the binary cipher text
     */
    public function encrypt(string $input): string
    {
		$encryption_algorithm = 'AES-128-CBC';
		$hashing_algorith = 'sha256';
    	$secret = $this->getCallbackSecret();
        $key = hash($hashing_algorith, $secret, true);
        $iv = substr($secret, 0, 16);

        $cipherText = openssl_encrypt(
            $input,
            $encryption_algorithm,
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        return bin2hex($cipherText);
    }

	/*
	 * Add a new order to the db
	 */
	public function newOrder($amount, $id_order) {

		try {
			$existing_order = Capsule::table('blockonomics_bitcoin_orders')
				->where('id_order', $id_order)
				->where('addr', '')
				->where('value', $amount)
				->value('id_order');
		} catch (\Exception $e) {
				exit("Unable to select order from blockonomics_bitcoin_orders: {$e->getMessage()}");
		}

		if(!$existing_order) {
			try {
				Capsule::table('blockonomics_bitcoin_orders')->insert(
					[
						'id_order' => $id_order,
						'timestamp' => time(),
						'status' => -1,
						'value' => $amount
					]
				);
			} catch (\Exception $e) {
					exit("Unable to insert new order into blockonomics_bitcoin_orders: {$e->getMessage()}");
			}
		}		

		$crypted_id = $this->encrypt($id_order);

		return $crypted_id;
	}

	/*
	 * Get the order id using the order uuid
	 */	
	public function getOrderIdByHash($order_hash) {
		$order_id = $this->decrypt($order_hash);
		try {
			$first_order = Capsule::table('blockonomics_bitcoin_orders')
				->where('id_order', $order_id)
				->orderBy('timestamp', 'desc')
				->first();
		} catch (\Exception $e) {
				exit("Unable to get order from blockonomics_bitcoin_orders: {$e->getMessage()}");
		}

		return $first_order->id_order;
	}

	/*
	 * Get all orders linked to uuid
	 */	
	public function getAllOrdersByHash($order_hash) {
		$order_id = $this->decrypt($order_hash);
		try {
			return Capsule::table('blockonomics_bitcoin_orders')
				->where('id_order', $order_id)
				->orderBy('timestamp', 'desc')->get();
		} catch (\Exception $e) {
				exit("Unable to get orders from blockonomics_bitcoin_orders: {$e->getMessage()}");
		}
	}

	/*
	 * Check for pending orders and return if exists
	 */	
	public function getPendingOrder($orders) {
		foreach ($orders as $order) {
			//check if status 0 or 1
			if($order->status == 0 || $order->status == 1){
				return $order;
			}
		}
	}

	/*
	 * Check for unused address for the currency linked to the order
	 */		
	public function getAndUpdateWaitingOrder($orders, $blockonomics_currency) {
		foreach ($orders as $order) {
			//check for currency address already waiting
			if($order->blockonomics_currency == $blockonomics_currency && $order->status == -1){
				//Check if existing order is expired
				$current_time = time();
				$total_time = $this->getTimePeriod() * 60;
				$clock = $order->timestamp + $total_time - $current_time;
				if($clock < 0){
					$order->bits = $this->convertFiatToBlockonomicsCurrency($order->value, $order->blockonomics_currency);
					$order->timestamp = $current_time;
				}
				$this->updateOrderExpected($order->addr, $order->blockonomics_currency, $order->timestamp, $order->bits);
				return $order;
			}
		}
	}

	/*
	 * Check for unused address or create new
	 */	
	public function processNewOrder($orders, $blockonomics_currency) {
		foreach ($orders as $order) {
			//check for new blank order in db
			if($order->addr == ""){
				$new_addresss_response = $this->getNewAddress($blockonomics_currency);
				if ($new_addresss_response->response_code == 200){
					$order->addr = $new_addresss_response->address;
				}else{
					exit($new_addresss_response->message);
				}

				$this->updateOrderAddress($order->id_order, $order->addr, $blockonomics_currency);
				$order->blockonomics_currency = $blockonomics_currency;
				$order->bits = $this->convertFiatToBlockonomicsCurrency($order->value, $order->blockonomics_currency);
				$order->timestamp = time();
				$this->updateOrderExpected($order->addr, $order->blockonomics_currency, $order->timestamp, $order->bits);
				return $order;
			}
		}
		// blank order already used, create a new blank order with same uuid
		// fix this
		$order_uuid = $this->newOrder($orders[0]->value, $orders[0]->id_order);
		$order = $this->processOrderHash($order_uuid, $blockonomics_currency);
		return $order;
	}

	/*
	 * Find an existing order or create a new order
	 */	
	public function processOrderHash($order_hash, $blockonomics_currency) {
		// Fetch all orders by hash
		$all_orders_by_hash = $this->getAllOrdersByHash($order_hash);
		if(!$all_orders_by_hash){
			exit;
		}
		// Check for pending payments and return the order
		$pending_payment = $this->getPendingOrder($all_orders_by_hash);
		if($pending_payment){
			return $pending_payment;
		}
		// Check for existing address
		$address_waiting = $this->getAndUpdateWaitingOrder($all_orders_by_hash, $blockonomics_currency);
		if($address_waiting){
			$address_waiting->currency = getCurrency(getClientsDetails()['user_id'])['code'];
			return $address_waiting;
		}
		// Process a new order for the hash and blockonomics currency
		$new_order = $this->processNewOrder($all_orders_by_hash, $blockonomics_currency);
		if($new_order){
			$new_order->currency = getCurrency(getClientsDetails()['user_id'])['code'];
			return $new_order;
		}
	}

	/*
	 * Try to get order row from db by address
	 */
	public function getOrderByAddress($bitcoinAddress) {
		try {
			$existing_order = Capsule::table('blockonomics_bitcoin_orders')
				->where('addr', $bitcoinAddress)
				->first();
		} catch (\Exception $e) {
				exit("Unable to select order from blockonomics_bitcoin_orders: {$e->getMessage()}");
		}

		$row_in_array = array(
			"id" => $existing_order->id,
			"order_id" => $existing_order->id_order,
			"timestamp"=> $existing_order->timestamp,
			"status" => $existing_order->status,
			"value" => $existing_order->value,
			"bits" => $existing_order->bits,
			"bits_payed" => $existing_order->bits_payed,
			"blockonomics_currency" => $existing_order->blockonomics_currency
		);

		return $row_in_array;
	}

	/*
	 * Try to get order row from db by order id
	 */
	public function getOrderById($orderId) {
		try {
			$existing_order = Capsule::table('blockonomics_bitcoin_orders')
				->where('id_order', $orderId)
				->orderBy('timestamp', 'desc')
				->first();
		} catch (\Exception $e) {
				exit("Unable to select order from blockonomics_bitcoin_orders: {$e->getMessage()}");
		}

		$row_in_array = array(
			"id" => $existing_order->id,
			"order_id" => $existing_order->id_order,
			"address"=> $existing_order->addr,
			"bits" => $existing_order->bits,
			"status" => $existing_order->status,
			"txid" => $existing_order->txid,
			"timestamp" => $existing_order->timestamp
		);

		return $row_in_array;
	}

	/*
	 * Update existing order information. Use BTC payment address as key
	 */
	public function updateOrderInDb($addr, $txid, $status, $bits_payed) {
		try {
			Capsule::table('blockonomics_bitcoin_orders')
					->where('addr', $addr)
					->update([
						'txid' => $txid,
						'status' => $status,
						'bits_payed' => $bits_payed
					]
				);
			} catch (\Exception $e) {
				exit("Unable to update order to blockonomics_bitcoin_orders: {$e->getMessage()}");
		}
	}

	/*
	 * Update existing order's expected amount and FIAT amount. Use WHMCS invoice id as key
	 */
	public function updateOrderExpected($address, $blockonomics_currency, $timestamp, $bits) {
		try {
			Capsule::table('blockonomics_bitcoin_orders')
					->where('addr', $address)
					->update([
						'blockonomics_currency' => $blockonomics_currency,
						'bits' => $bits,
						'timestamp' => $timestamp
					]
				);
		} catch (\Exception $e) {
			exit("Unable to update order to blockonomics_bitcoin_orders: {$e->getMessage()}");
		}
	}

	/*
	 * Update existing order's address. Set status, txid and bits_payed to default values. Use WHMCS invoice id as key
	 */
	public function updateOrderAddress($order_id, $address, $blockonomics_currency) {
		try {
			Capsule::table('blockonomics_bitcoin_orders')
					->where('id_order', $order_id)
					->where('addr', '')
					->where('blockonomics_currency', '')
					->update([
						'addr' => $address,
						'blockonomics_currency' => $blockonomics_currency
					]
				);
		} catch (\Exception $e) {
			exit("Unable to update order to blockonomics_bitcoin_orders: {$e->getMessage()}");
		}
	}

	/*
	 * Get URL of the WHMCS installation
	 */
	public function getSystemUrl() {
		return Capsule::table('tblconfiguration')
				->where('setting', 'SystemURL')
				->value('value');
	}

	/*
	 * Check for errors in request response
	 */
	public function checkForErrors($responseObj) {
		if(!isset($responseObj->response_code)) {
				$error = true;
		} else {
				switch ($responseObj->response_code) {
					case '200':
							break;
					default:
							$error = true;
							break;
				}
		}
		if(isset($error)) {
			return $error;
		}
		// No errors
		return false;
	}

	/*
	 * Make a request using curl
	 */
	public function doCurlCall($url, $post_content='') {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if ($post_content)
		{
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_content);
		}
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Authorization: Bearer '. $this->getApiKey(),
				'Content-type: application/x-www-form-urlencoded'
			));
		$data = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		$responseObj = new \stdClass();
		$responseObj->data = json_decode($data);
		$responseObj->response_code = $httpcode;
		return $responseObj;
	}

	/*
	 * Run the test setup
	 */
	public function testSetup($new_api)	{

		$xpub_fetch_url = 'https://www.blockonomics.co/api/address?&no_balance=true&only_xpub=true&get_callback=true';
		$set_callback_url = 'https://www.blockonomics.co/api/update_callback';
		$error_str = '';

		$response = $this->doCurlCall($xpub_fetch_url);

		$callback_url = $this->getCallbackUrl();
		$api_key = $this->getApiKey();
		if ($api_key != $new_api) {
			$error_str = 'New API Key: Save your changes and then click \'Test Setup\'';//API key changed
		}
		elseif (!isset($response->response_code)) {
			$error_str = 'Your server is blocking outgoing HTTPS calls';
		}
		elseif ($response->response_code==401)
			$error_str = 'API Key is incorrect';
		elseif ($response->response_code!=200)
			$error_str = $response->data;
		elseif (!isset($response->data) || count($response->data) == 0)
		{
			$error_str = 'You have not entered an xpub';
		}
		elseif (count($response->data) == 1)
		{
			if(!$response->data[0]->callback || $response->data[0]->callback == null)
			{
				//No callback URL set, set one 
				$post_content = '{"callback": "'.$callback_url.'", "xpub": "'.$response->data[0]->address.'"}';
				$this->doCurlCall($set_callback_url, $post_content);  
			}
			elseif($response->data[0]->callback != $callback_url)
			{
				// Check if only secret differs
				$base_url = substr($callback_url, 0, -48);
				if(strpos($response->data[0]->callback, $base_url) !== false)
				{
					//Looks like the user regenrated callback by mistake
					//Just force Update_callback on server
					$post_content = '{"callback": "'.$callback_url.'", "xpub": "'.$response->data[0]->address.'"}';
					$this->doCurlCall($set_callback_url, $post_content);  
				}
				else
					$error_str = "Your have an existing callback URL. Refer instructions on integrating multiple websites";
			}
		}
		else 
		{
			$error_str = "Your have an existing callback URL or multiple xPubs. Refer instructions on integrating multiple websites";

			foreach ($response->data as $resObj)
				if($resObj->callback == $callback_url)
					// Matching callback URL found, set error back to empty
					$error_str = '';
		}

		if ($error_str == '') {
			// Test new address generation
			$new_addresss_response = $this->getNewAddress('btc',true);
			if ($new_addresss_response->status != 200){
				$error_str = $new_addresss_response->message;
			}
		}

		return $error_str;
	}
}