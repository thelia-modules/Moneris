<?php
/**
* This class has been generated by TheliaStudio
* For more information, see https://github.com/thelia-modules/TheliaStudio
*/

namespace Moneris\Controller\Base;

use Moneris\Moneris;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Form\Exception\FormValidationException;
use Moneris\Model\Config\MonerisConfigValue;

/**
 * Class MonerisConfigController
 * @package Moneris\Controller\Base
 * @author TheliaStudio
 */
class MonerisConfigController extends BaseAdminController
{
    public function defaultAction()
    {
        return $this->render("moneris-configuration");
    }

    public function saveAction()
    {
        $baseForm = $this->createForm("moneris.configuration");

        $errorMessage = null;

        try {
            $form = $this->validateForm($baseForm);
            $data = $form->getData();

            Moneris::setConfigValue(MonerisConfigValue::STORE_ID, is_bool($data["store_id"]) ? (int) ($data["store_id"]) : $data["store_id"]);
            Moneris::setConfigValue(MonerisConfigValue::API_TOKEN, is_bool($data["api_token"]) ? (int) ($data["api_token"]) : $data["api_token"]);
        } catch (FormValidationException $ex) {
            // Invalid data entered
            $errorMessage = $this->createStandardFormValidationErrorMessage($ex);
        } catch (\Exception $ex) {
            // Any other error
            $errorMessage = $this->getTranslator()->trans('Sorry, an error occurred: %err', ['%err' => $ex->getMessage()], [], Moneris::DOMAIN);
        }

        if (null !== $errorMessage) {
            // Mark the form as with error
            $baseForm->setErrorMessage($errorMessage);

            // Send the form and the error to the parser
            $this->getParserContext()
                ->addForm($baseForm)
                ->setGeneralError($errorMessage)
            ;
        } else {
            $this->getParserContext()
                ->set("success", true)
            ;
        }

        return $this->defaultAction();
    }
}
