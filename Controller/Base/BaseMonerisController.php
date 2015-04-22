<?php

namespace Moneris\Controller\Base;

use Moneris\Form\MonerisPaymentForm;
use Moneris\Resource\mpgClasses\mpgHttpsPost;
use Moneris\Resource\mpgClasses\mpgRequest;
use Moneris\Resource\mpgClasses\mpgTransaction;
use Moneris\Resource\mpgClasses\mpgCvdInfo;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Form\Exception\FormValidationException;
use Thelia\Model\ModuleConfigI18nQuery;
use Thelia\Model\ModuleQuery;
use Thelia\Model\ModuleConfigQuery;

use Moneris\Resource\Moneris;

/**
 * Class BaseMonerisController
 * @package Moneris\Controller\Base
 * @author Etienne PERRIERE <eperriere@openstudio.fr> - OpenStudio
 */
class BaseMonerisController extends BaseAdminController {

    public function paymentAction()
    {
        // Initialize vars
        $request = $this->getRequest();
        $mpf = new MonerisPaymentForm($request);

        try {
            // Form verification
            $form = $this->validateForm($mpf);
            $data = $form->getData();

            // Build and execute transaction
            // METHODE AVANT DE TESTER LE MODULE GITHUB - $transaction = $this->processTransaction($data, $request->getSession());

            $config = array(
                'api_key' => 'yesguy',
                'store_id' => 'store5',
                'environment' => Moneris::ENV_TESTING,
                'require_cvd' => true,
                'cvd_codes' => array('M', 'Y', 'P', 'S', 'U')
            );

            $moneris = Moneris::create($config);

            // Transaction parameters
            $params = array(
                'cc_number' => '4761739012345611',
                'order_id' => 'OPENSTUDIO-'.date("dmy-G:i:s"),
                'amount' => '10.30',
                'expiry_month' => $data['expiryMonth'],
                'expiry_year' => $data['expiryYear'],
                'cust_id' => $request->getSession()->getCustomerUser()->getRef(),
                'cvd' => $data['cvdValue']
            );

            // Send transaction
            $purchase_result = $moneris->purchase($params);

            $errors = array();

            // Check transaction state
            if ($purchase_result->was_successful() && ( $purchase_result->failed_avs() || $purchase_result->failed_cvd() ))
            {
                $errors[] = $purchase_result->error_message();
                $void = $moneris->void($purchase_result->transaction());
            }
            else if (! $purchase_result->was_successful())
            {
                $errors[] = $purchase_result->error_message();
            }
            else
            {
                $transaction = $purchase_result->transaction();

            }

            return $this->render('order-placed');

        } catch (FormValidationException $e) {
            throw new \Exception($this->createStandardFormValidationErrorMessage($e));
        }
    }

    public function processTransaction($data, $session)
    {
        // Get module config information

        $moduleId = ModuleQuery::create()
            ->select('Id')
            ->findOneByCode('Moneris');

        $moduleConfig = ModuleConfigQuery::create()
            ->findByModuleId($moduleId);

        foreach ($moduleConfig as $mc) {
            if ($mc->getName() === "api_token") {
                $api_token = ModuleConfigI18nQuery::create()
                    ->select("Value")
                    ->findOneById($mc->getId());
            } elseif ($mc->getName() === "store_id") {
                $store_id = ModuleConfigI18nQuery::create()
                    ->select("Value")
                    ->findOneById($mc->getId());
            }
        }

        // Transactional Variables

        $type='purchase';
        $orderId='OPENSTUDIO-'.date("dmy-G:i:s");
        $custId = $session->getCustomerUser()->getRef();
        /* A REMETTRE $amount = (string)$session->getSessionCart()->getTaxedAmount($this->container->get('thelia.taxEngine')->getDeliveryCountry()); */
        $amount = '10.36';
        /* A REMETTRE $pan = $data['pan']; */
        $pan = '4761739012345611';
        $expiryDate = $data['expiryYear'].$data['expiryMonth'];
        $crypt='7';
        $dynamic_descriptor='123';
        //$status_check = 'false';

        // CVD Variables

        $cvdValue = $data['cvdValue'];
        $cvdIndicator = '1';

        // CVD Associative Array

        $cvdTemplate = array('cvd_indicator' => $cvdIndicator,
            'cvd_value' => $cvdValue
        );

        // CVD Object

        $mpgCvdInfo = new mpgCvdInfo($cvdTemplate);

        // Transactional Associative Array

        $txnArray = array(
            'type'=>$type,
            'order_id'=>$orderId,
            'cust_id'=>$custId,
            'amount'=>$amount,
            'pan'=>$pan,
            'expdate'=>$expiryDate,
            'crypt_type'=>$crypt,
            'dynamic_descriptor'=>$dynamic_descriptor
        );

        // Transaction Object

        $mpgTxn = new mpgTransaction($txnArray);

        // Set CVD

        $mpgTxn->setCvdInfo($mpgCvdInfo);

        // Request Object

        $mpgRequest = new mpgRequest($mpgTxn);

        // HTTPS Post Object

        $mpgHttpPost = new mpgHttpsPost($store_id,$api_token,$mpgRequest);

        // Response

        $mpgResponse = $mpgHttpPost->getMpgResponse();

        print("<br>CardType = " . $mpgResponse->getCardType());
        print("<br>TransAmount = " . $mpgResponse->getTransAmount());
        print("<br>TxnNumber = " . $mpgResponse->getTxnNumber());
        print("<br>ReceiptId = " . $mpgResponse->getReceiptId());
        print("<br>TransType = " . $mpgResponse->getTransType());
        print("<br>ReferenceNum = " . $mpgResponse->getReferenceNum());
        print("<br>ResponseCode = " . $mpgResponse->getResponseCode());
        print("<br>ISO = " . $mpgResponse->getISO());
        print("<br>Message = " . $mpgResponse->getMessage());
        print("<br>IsVisaDebit = " . $mpgResponse->getIsVisaDebit());
        print("<br>AuthCode = " . $mpgResponse->getAuthCode());
        print("<br>Complete = " . $mpgResponse->getComplete());
        print("<br>TransDate = " . $mpgResponse->getTransDate());
        print("<br>TransTime = " . $mpgResponse->getTransTime());
        print("<br>Ticket = " . $mpgResponse->getTicket());
        print("<br>TimedOut = " . $mpgResponse->getTimedOut());
        print("<br>StatusCode = " . $mpgResponse->getStatusCode());
        print("<br>StatusMessage = " . $mpgResponse->getStatusMessage());
die();
        return $mpgResponse;
    }
}