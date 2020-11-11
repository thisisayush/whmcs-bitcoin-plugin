# Blockonomics WHMCS plugin #
Accept bitcoins on your WHMCS, payments go directly into your wallet

## Description ##
- Accept bitcoin payments on your website with ease
- No security risk, payments go directly into your own bitcoin wallet

## Installation ##
[Blog Tutorial](https://blog.blockonomics.co/friendly-bitcoin-payments-for-web-hosting-businesses-using-whmcs-88de8eef4e81) | [Video Tutorial](https://www.youtube.com/watch?v=jORcxsV-OOg)

- Copy the folder `modules` to your WHMCS directory. You don't need to upload any other folder or file like `screnshots` or `README.md`.
- Go to your WHMCS admin, Setup -> Payments -> Payment Gateways
- Activate Blockonomics in All Payment Gateways
- Set your API key in Manage Existing Gateways
- After setting API Key refresh page
- Copy your Callback to Blockonomics Merchants > Settings

## Upgrade from 1.8.X to 1.9.X ##
The entire file structure has been refactored to match the WHMCS code standard and the code has been ported to comply the syntax PSR2 for PHP.

- Just upload and replace the folder `modules` in your own WHMCS installation with the one we provide. You don't need to upload any other folder or file like `screnshots` or `README.md`.
- Now just execute the script updater.php using your browser. Example: https://xxxxxxx.ccc/modules/gateways/blockonomics/updater.php (replace xxxxxxx.ccc with your own WHMCS domain)
- That's it!

## Screenshots ##

![](screenshots/screenshot-1.png)
Checkout option

![](screenshots/screenshot-2.png)
Payment screen

![](screenshots/screenshot-3.png)
Blockonomics configuration
