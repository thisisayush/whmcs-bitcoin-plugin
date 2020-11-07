# Blockonomics WHMCS plugin #
Accept bitcoins on your WHMCS, payments go directly into your wallet

## Description ##
- Accept bitcoin payments on your website with ease
- No security risk, payments go directly into your own bitcoin wallet

## Installation ##
[Blog Tutorial](https://blog.blockonomics.co/friendly-bitcoin-payments-for-web-hosting-businesses-using-whmcs-88de8eef4e81) | [Video Tutorial](https://www.youtube.com/watch?v=jORcxsV-OOg)

- Copy the files to your WHMCS directory
- Go to your WHMCS admin, Setup -> Payments -> Payment Gateways
- Activate Blockonomics in All Payment Gateways
- Set your API key in Manage Existing Gateways
- After setting API Key refresh page
- Copy your Callback to Blockonomics Merchants > Settings

## Upgrade from 1.8.X to 1.9 ##
The entire file structure has been refactored to match the WHMCS code standard and the code has been ported to comply the syntax PSR2 for PHP.
- Delete the folders css/ img/ js/ located in your WHMCS root installation. They are now located inside the module.
- Delete the files payment.php testSetup.php located in your WHMCS root installation.
- Delete the folder templates/blockonomics since the template now is located inside the module.
- Replace using the content located in modules/gateways into your WHMCS.

## Screenshots ##

![](screenshots/screenshot-1.png)
Checkout option

![](screenshots/screenshot-2.png)
Payment screen

![](screenshots/screenshot-3.png)
Blockonomics configuration
