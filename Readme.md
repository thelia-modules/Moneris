# Moneris

Moneris provides you a way to link your website with your Moneris merchant account and allows users to use this solution for secure payment.

## Installation

### Manually

* Copy the module into ```<thelia_root>/local/modules/``` directory and be sure that the name of the module is Moneris.
* Activate it in your Thelia administration panel.

## Usage

Once activated, click the plugin's Configure button, then enter your Moneris merchant account information.
Feel free to modify the Moneris/templates/frontOffice/default/card-form.html or moneris-receipt.html to display information as you want.

## Hook

This plugin's hooks display information on two front office places :
- order-payment-gateway.body : after selecting Moneris payment, display the bank card form
- order-placed.body : once the transaction is over, display a summary

## Other ?

### Selecting environment

Into MonerisController, line 67, think about setting the good environment :

$config = array(
    'api_key' => $apiToken,
    'store_id' => $storeId,
    'environment' => MonerisApi::ENV_LIVE,
    'require_cvd' => true,
    'cvd_codes' => array('M', 'Y', 'P', 'S', 'U')
);

ENV_TESTING // use the mock API
ENV_STAGING // use the API sandbox
ENV_LIVE    // use the live API server

If you need to implement Address Verification Service (AVS), refere both IronKeith moneris' API's improvement at https://github.com/ironkeith/moneris-eselectplus-api and moneris developer guidelines at https://developer.moneris.com/

### Testing the solution

To test if the plugin is able to communicate with Moneris servers :
- set 'api_key' to 'yesguy'
- set 'store_id' to 'store5'
- set 'environment' to 'MonerisApi::ENV_TESTING'
- use 4242424242424242 as bank card number (it will be considered as a Visa)
- set the amount between $10.30 - $10.34 to test an approved transaction
- set the amount between $10.35 - $10.37 to test a declined transaction
- connect to Moneris test store at https://esqa.moneris.com/mpg/ and use provided credentials (use 'store5' as Store ID)
- retrieve your test in the 'REPORTS' tab -> 'transactions' subtab
