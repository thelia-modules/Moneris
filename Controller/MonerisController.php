<?php

namespace Moneris\Controller;

use Moneris\Moneris;
use Moneris\Form\MonerisPaymentForm;
use Moneris\Resource\MonerisApi;
use Thelia\Core\HttpKernel\Exception\RedirectException;
use Thelia\Form\Exception\FormValidationException;
use Thelia\Model\ConfigQuery;
use Thelia\Model\OrderQuery;
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
        $errorMessage = null;

        try {
            // Form verification
            $form = $this->validateForm($mpf);
            $data = $form->getData();
            $session = $this->getSession();

            // Proceed payment
            $transactionOptions = $this->getTransactionOptions($data, $session);
            $this->processTransaction($transactionOptions, $session);

        } catch (FormValidationException $e) {
            $errorMessage = $e->getMessage();
        } catch (RedirectException $e) {
            throw $e;
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
        }

        $this->redirectToFailurePage($this->getSession()->get(Moneris::MONERIS_ORDER_ID, false), $errorMessage);
    }

    /**
     * @param $data
     * @param $session
     * @return array
     */
    public function getTransactionOptions($data, $session)
    {
        // Get module config information
        $storeId = Moneris::getConfigValue('store_id');
        $apiToken = Moneris::getConfigValue('api_token');

        // Set transaction configuration
        $config = array(
            'api_key' => $apiToken,
            'store_id' => $storeId,
            'environment' => MonerisApi::ENV_LIVE,
            'require_cvd' => true,
            'cvd_codes' => array('M', 'Y', 'P', 'S', 'U')
        );

        $orderRef = $session->get(Moneris::MONERIS_ORDER_REF, false);
        $amount = (string)$session->getSessionCart()->getTaxedAmount($this->container->get('thelia.taxEngine')->getDeliveryCountry());
        $custId = $session->getCustomerUser()->getRef();

        // Set transaction parameters
        $params = array(
            'cc_number'     => $data['pan'],
            'order_id'      => $orderRef,
            'amount'        => $amount,
            'expiry_month'  => $data['expiryMonth'],
            'expiry_year'   => $data['expiryYear'],
            'cust_id'       => $custId,
            'cvd'           => $data['cvdValue']
        );

        return ['config' => $config, 'params' => $params];
    }

    /**
     * @param $transactionOptions
     * @param $session
     * @throws \Exception
     */
    public function processTransaction($transactionOptions, $session)
    {
        // Build & send transaction
        $moneris = MonerisApi::create($transactionOptions['config']);
        $purchaseResult = $moneris->purchase($transactionOptions['params']);

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
            // Everything is OK ! Get transaction details to be displayed

            $transaction = $purchaseResult->transaction();

            $merchant = ConfigQuery::create()->getStoreName();
            $merchantUrl = ConfigQuery::create()->getConfiguredShopUrl();

            $flashBag = $session->getFlashBag();

            $flashBag->set('type', $transaction->params()['type']);
            $flashBag->set('date', $transaction->response()->receipt[0]->TransDate.' '.$transaction->response()->receipt[0]->TransTime);
            $flashBag->set('responseCode', (string)$transaction->response()->receipt[0]->ResponseCode);
            $flashBag->set('authCode', (string)$transaction->response()->receipt[0]->AuthCode);
            $flashBag->set('isoCode', (string)$transaction->response()->receipt[0]->ISO);
            $flashBag->set('message', (string)$transaction->response()->receipt[0]->Message);
            $flashBag->set('ref', (string)$transaction->response()->receipt[0]->ReferenceNum);
            $flashBag->set('merchant', $merchant);
            $flashBag->set('merchantUrl', $merchantUrl);

            // Set order status to 'paid'
            $this->confirmPayment($orderId);

            // Set order transaction reference
            $order = OrderQuery::create()->findOneById($orderId);
            $order->setTransactionRef((string)$transaction->response()->receipt[0]->ReferenceNum);
            $order->save();

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