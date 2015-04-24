# Moneris

Moneris provides a way to link your website with your Moneris merchant account and allows users to use this solution for secure payment.

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

If you need to implement Address Verification Service (AVS), refer both IronKeith's Moneris API improvement at https://github.com/ironkeith/moneris-eselectplus-api and Moneris developer guidelines at https://developer.moneris.com/

### Testing the solution

To test if the plugin can communicate with Moneris servers :
In the backoffice plugin configuration
- set 'API token' to 'yesguy'
- set 'Store ID' to 'store5'
- select API sandbox environment
At Moneris test environment (https://esqa.moneris.com/mpg/)
- connect to Moneris test store at  and use provided credentials (use 'store5' as Store ID)
- retrieve your test in the 'REPORTS' tab -> 'transactions' subtab
