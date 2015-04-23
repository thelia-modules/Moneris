# Moneris

Moneris provides you a way to link your website with your Moneris merchant account and allows users to use this solution for secure payment.

## Installation

### Manually

* Copy the module into ```<thelia_root>/local/modules/``` directory and be sure that the name of the module is Moneris.
* Activate it in your Thelia administration panel.

## Usage

One activated, click the plugin's Configure button, then enter your Moneris merchant account information.

## Hook

This plugin's hooks display information on two front office places :
- order-payment-gateway.body : after selecting Moneris payment, display the bank card form
- order-placed.body : once the transaction is over, display a summary

## Other ?

Into MonerisController, line 56, think about set the good environment :

$config = array(
    'api_key' => $apiToken,
    'store_id' => $storeId,
    'environment' => MonerisApi::ENV_TESTING,
    'require_cvd' => true,
    'cvd_codes' => array('M', 'Y', 'P', 'S', 'U')
);

ENV_TESTING // use the mock API
ENV_STAGING // use the API sandbox
ENV_LIVE    // use the live API server

If you need to implement Address Verification Service (AVS), refere both IronKeith moneris' API's improvement at https://github.com/ironkeith/moneris-eselectplus-api and moneris developer guidelines at https://developer.moneris.com/