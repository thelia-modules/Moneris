<?php
/**
* This class has been generated by TheliaStudio
* For more information, see https://github.com/thelia-modules/TheliaStudio
*/

namespace Moneris\Form;

use Moneris\Moneris;
use Moneris\Form\Base\MonerisConfigForm as BaseMonerisConfigForm;

/**
 * Class MonerisConfigForm
 * @package Moneris\Form\Base
 */
class MonerisConfigForm extends BaseMonerisConfigForm
{
    public function getTranslationKeys()
    {
        return array(
            "store_id" => $this->translator->trans("Store id", [], Moneris::MESSAGE_DOMAIN),
            "api_token" => $this->translator->trans("Api token", [], Moneris::MESSAGE_DOMAIN),
            "environment" => $this->translator->trans("Environment", [], Moneris::MESSAGE_DOMAIN)
        );
    }
}
