<link rel="stylesheet" type="text/css" href="css/order.css">
<link rel="stylesheet" type="text/css" href="css/icons/icons.css">
<link rel="stylesheet" type="text/css" href="css/cryptofont/cryptofont.min.css">

<div id="system-url" data-url="{$system_url}"></div>
<div id="time_period" data-time_period="{$time_period}"></div>
<div id="active_currencies" data-active_currencies='{$active_currencies}'></div>
<div id="order_uuid" data-order_uuid="{$order_uuid}"></div>
<div id="order_id" data-order_id="{$order_id}"></div>

<div ng-app="shopping-cart-demo">
  <div ng-controller="CheckoutController">
    <div class="bnomics-order-container">
      <!-- Heading row -->
      <div class="bnomics-order-heading">
        <div class="bnomics-order-heading-wrapper">
          <div class="bnomics-order-id">
            <span class="bnomics-order-number" ng-cloak> Order #[[order_id]]</span>
          </div>
        </div>
      </div>
      <!-- Spinner -->
      <div class="bnomics-spinner-wrapper" ng-show="spinner" ng-cloak>
        <div class="bnomics-spinner"></div>
      </div>
      <!-- Address Error -->
      <div id="address-error" ng-show="address_error" ng-cloak>
        <h2>Could not generate new bitcoin address.</h2>
        <p>Note to webmaster: Please login to admin and go to Setup > Payments > Payment Gateways > Manage Existing Gateways and use the Test Setup button to diagnose the error.</p>
      </div>
      <!-- Pending payment -->
      <div id="pending-error" ng-show="pending_error" ng-cloak>
        <h2>Payment is pending</h2>
        <i>Additional payments to invoice are only allowed after current pending transaction is confirmed. Monitor the transaction here: 
        <a href="https://www.blockonomics.co/api/tx?txid=[[txid]]" target="_blank">[[txid]]</a></i>
      </div>
      <!-- Blockonomics Currency Selecter -->
      <div class="bnomics-select-container" ng-show="currency_selecter" ng-cloak>
        <h2>Select Currency</h2>
        <p>Choose one of the following currency options to complete your payment</p>
        <hr>
        <h3 class="bnomics-select-options" ng-repeat="active_currency in active_currencies" ng-click="select_blockonomics_currency(active_currency.code)">
          <table width="100%">
            <tr>
                <td align="left"><img src="img/[[active_currency.code]].png" alt="[[active_currency.name]] Logo"> <b>[[active_currency.name]]</b></td>
                <td align="right"><span class="bnomics-select-currency-code">[[active_currency.code]]</span> ></td>
            </tr>
          </table>
        <hr></h3>
      </div>
      <!-- Payment Expired -->
      <div class="bnomics-order-expired-wrapper" ng-show="order.status == -3" ng-cloak>
        <h3 class="warning bnomics-status-warning">Payment Expired</h3><br>
        <p><a href="#" ng-click="try_again_click()">Click here to try again</a></p>
      </div>
      <!-- Payment Error -->
      <div class="bnomics-order-error-wrapper" ng-show="order.status == -2" ng-cloak>
        <h3 class="warning bnomics-status-warning">Payment Error</h3>
      </div>
      <!-- Blockonomics Checkout Panel -->
      <div class="bnomics-order-panel" ng-show="order.status == -1" ng-cloak>
        <h3 class="bnomics-blockonomics-currency">
          <img src="img/[[currency.code]].png" alt="[[currency.name]] Logo">
          [[currency.name]] Payment
        </h3>
        <div class="bnomics-order-info">
          <div class="bnomics-bitcoin-pane">
            <div class="bnomics-btc-info">
              <!-- Left Side -->
              <!-- QR and Open in wallet -->
              <div class="bnomics-qr-code">
                <div class="bnomics-qr">
                  <a href="[[currency.uri]]:[[order.addr]]?amount=[[order.bits/1.0e8]]" target="_blank">
                    <qrcode data="[[currency.uri]]:[[order.addr]]?amount=[[order.bits/1.0e8]]" size="160" version="6">
                      <canvas class="qrcode"></canvas>
                    </qrcode>
                  </a>
                </div>
                <div class="bnomics-qr-code-hint"><a href="[[currency.uri]]:[[order.addr]]?amount=[[order.bits/1.0e8]]" target="_blank">Open in wallet</a></div>
              </div>
              <!-- Right Side -->
              <div class="bnomics-amount">
                <div class="bnomics-bg">
                  <!-- Order Amounts -->
                  <div class="bnomics-amount">
                    <div class="bnomics-amount-text" ng-hide="amount_copyshow" ng-cloak>To pay, send exactly this [[currency.code | uppercase]] amount</div>
                    <div class="bnomics-copy-amount-text" ng-show="amount_copyshow" ng-cloak>Copied to clipboard</div>
                    <ul ng-click="blockonomics_amount_click()" id="bnomics-amount-input" class="bnomics-amount-input">
                        <li id="bnomics-amount-copy">[[order.bits/1.0e8]]</li>
                        <li>[[order.blockonomics_currency | uppercase]]</li>
                        <li class="bnomics-grey"> ≈ </li>
                        <li class="bnomics-grey">[[order.value]]</li>
                        <li class="bnomics-grey">[[order.currency]]</li>
                    </ul>
                    <!-- <input ng-click="blockonomics_amount_click()" id="bnomics-amount-input" class="bnomics-amount-input" type="text" ng-value="amount_string" readonly="readonly" style="cursor: pointer;"> -->
                  </div>
                  <!-- Order Address -->
                  <div class="bnomics-address">
                    <div class="bnomics-address-text" ng-hide="address_copyshow" ng-cloak>To this [[currency.name | lowercase]] address</div>
                    <div class="bnomics-copy-address-text" ng-show="address_copyshow" ng-cloak>Copied to clipboard</div>
                    <ul ng-click="blockonomics_address_click()" id="bnomics-address-input" class="bnomics-address-input">
                          <li id="bnomics-address-copy">[[order.addr]]</li>
                    </ul>
                  </div>
                  <!-- Order Countdown Timer -->
                  <div class="bnomics-progress-bar-wrapper">
                    <div class="bnomics-progress-bar-container">
                      <div class="bnomics-progress-bar" style="width: [[progress]]%;"></div>
                    </div>
                  </div>
                  <span class="ng-cloak bnomics-time-left">[[clock*1000 | date:'mm:ss' : 'UTC']] min</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Blockonomics How to pay + Credit -->
      <div class="bnomics-powered-by">
        <a href="https://blog.blockonomics.co/how-to-pay-a-bitcoin-invoice-abf4a04d041c" target="_blank">How do I pay?</a><br>
        <div class="bnomics-powered-by-text bnomics-grey" >Powered by Blockonomics</div>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript" src="js/angular.min.js"></script>
<script type="text/javascript" src="js/angular-resource.min.js"></script>
<script type="text/javascript" src="js/app.js"></script>
<script type="text/javascript" src="js/qrcode-generator/qrcode.js"></script>
<script type="text/javascript" src="js/qrcode-generator/qrcode_UTF8.js"></script>
<script type="text/javascript" src="js/angular-qrcode/angular-qrcode.js"></script>
<script type="text/javascript" src="js/reconnecting-websocket.min.js"></script>
