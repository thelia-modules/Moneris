<?php
/**
* This class has been generated by TheliaStudio
* For more information, see https://github.com/thelia-modules/TheliaStudio
*/

namespace Moneris\Form\Base;

use Moneris\Moneris;
use Thelia\Form\BaseForm;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class MonerisErrorsCreateForm
 * @package Moneris\Form\Base
 * @author TheliaStudio
 */
class MonerisErrorsCreateForm extends BaseForm
{
    const FORM_NAME = "moneris_errors_create";

    public function buildForm()
    {
        $translationKeys = $this->getTranslationKeys();
        $fieldsIdKeys = $this->getFieldsIdKeys();

        $this->addOrderIdField($translationKeys, $fieldsIdKeys);
        $this->addMessageField($translationKeys, $fieldsIdKeys);
    }

    protected function addOrderIdField(array $translationKeys, array $fieldsIdKeys)
    {
        $this->formBuilder->add("order_id", "integer", array(
            "label" => $this->translator->trans($this->readKey("order_id", $translationKeys), [], Moneris::MESSAGE_DOMAIN),
            "label_attr" => ["for" => $this->readKey("order_id", $fieldsIdKeys)],
            "required" => false,
            "constraints" => array(
            ),
            "attr" => array(
            )
        ));
    }

    protected function addMessageField(array $translationKeys, array $fieldsIdKeys)
    {
        $this->formBuilder->add("message", "textarea", array(
            "label" => $this->translator->trans($this->readKey("message", $translationKeys), [], Moneris::MESSAGE_DOMAIN),
            "label_attr" => ["for" => $this->readKey("message", $fieldsIdKeys)],
            "required" => false,
            "constraints" => array(
            ),
            "attr" => array(
            )
        ));
    }

    public function getName()
    {
        return static::FORM_NAME;
    }

    public function readKey($key, array $keys, $default = '')
    {
        if (isset($keys[$key])) {
            return $keys[$key];
        }

        return $default;
    }

    public function getTranslationKeys()
    {
        return array();
    }

    public function getFieldsIdKeys()
    {
        return array(
            "order_id" => "moneris_errors_order_id",
            "message" => "moneris_errors_message",
        );
    }
}
