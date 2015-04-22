<?php

namespace Moneris\Controller;

use Moneris\Moneris;
use Moneris\Form\MonerisPaymentForm;
use Moneris\Resource\MonerisApi;
use Thelia\Form\Exception\FormValidationException;
use Thelia\Module\BasePaymentModuleController;

/**
 * Class MonerisController
 * @package Moneris\Controller
 * @author Etienne PERRIERE <eperriere@openstudio.fr> - OpenStudio
 */
class MonerisController extends BasePaymentModuleController {

    /**
     * @throws \Exception
     */
    public function paymentAction()
    {
        // Initialize vars
        $request = $this->getRequest();
        $mpf = new MonerisPaymentForm($request);

        try {
            // Form verification
            $form = $this->validateForm($mpf);
            $data = $form->getData();

            // Proceed payment
            $this->processTransaction($data, $this->getSession());

        } catch (FormValidationException $e) {
            throw new \Exception($this->createStandardFormValidationErrorMessage($e));
        }
    }

    /**
     * @param $data
     * @param $session
     * @throws \Exception
     */
    public function processTransaction($data, $session)
    {
        // Get module config information
        $storeId = Moneris::getConfigValue('store_id');
        $apiToken = Moneris::getConfigValue('api_token');

        // Set transaction configuration
        $config = array(
            'api_key' => $apiToken,
            'store_id' => $storeId,
            'environment' => MonerisApi::ENV_TESTING,
            'require_cvd' => true,
            'cvd_codes' => array('M', 'Y', 'P', 'S', 'U')
        );

        // Set transaction parameters
        $params = array(
            'cc_number'     => $data['pan'],
            'order_id'      => 'OPENSTUDIO-'.date("dmy-G:i:s"),
            'amount'        => '10.33', //(string)$session->getSessionCart()->getTaxedAmount($this->container->get('thelia.taxEngine')->getDeliveryCountry()),
            'expiry_month'  => $data['expiryMonth'],
            'expiry_year'   => $data['expiryYear'],
            'cust_id'       => $session->getCustomerUser()->getRef(),
            'cvd'           => $data['cvdValue']
        );

        // Build & send transaction
        $moneris = MonerisApi::create($config);
        $purchaseResult = $moneris->purchase($params);

        $orderId = $session->get(Moneris::MONERIS_ORDER_ID, false);

        // Check transaction state
        if ($purchaseResult->was_successful() && ( $purchaseResult->failed_avs() || $purchaseResult->failed_cvd() ))
        {
            $errors = $purchaseResult->error_message();
            $moneris->void($purchaseResult->transaction());
            $this->redirectToFailurePage($orderId, $errors);
        }
        else if (! $purchaseResult->was_successful())
        {
            $errors = $purchaseResult->error_message();
            $this->redirectToFailurePage($orderId, $errors);
        }
        else
        {
            //$transaction = $purchaseResult->transaction();   // More information about the transaction
            $this->confirmPayment($orderId);
            $this->redirectToSuccessPage($orderId);
        }
    }

    /**
     * Return a module identifier used to calculate the name of the log file,
     * and in the log messages.
     *
     * @return string the module code
     */
    protected function getModuleCode()
    {
        return 'Moneris';
    }
}