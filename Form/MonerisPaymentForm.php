<?php

namespace Moneris\Form;

use Moneris\Moneris;
use Thelia\Form\BaseForm;
use Symfony\Component\Validator\Constraints;

/**
 * Class MonerisPaymentForm
 * @package Moneris\Form
 * @author Etienne PERRIERE <eperriere@openstudio.fr> - OpenStudio
 */
class MonerisPaymentForm extends BaseForm {

    public function getName()
    {
        return 'moneris_payment';
    }

    protected function buildForm()
    {
        $this->formBuilder
        ->add(
            "pan",
            "text",
            array(
                "constraints" => array(
                    new Constraints\NotBlank(),
                    new Constraints\Regex('/^\d{13,20}$/')
                ),
                "label" => $this->translator->trans('Bank card number', [], Moneris::DOMAIN.'.fo.default')
            )
        )
        ->add(
            "expiryYear",
            "text",
            array(
                "constraints" => array(
                    new Constraints\NotBlank(),
                    new Constraints\Regex('/^(\d{2})$/')
                ),
                "label" => $this->translator->trans('Expiry year', [], Moneris::DOMAIN.'.fo.default')
            )
        )
        ->add(
            "expiryMonth",
            "text",
            array(
                "constraints" => array(
                    new Constraints\NotBlank(),
                    new Constraints\Regex('/^(0[1-9]|1[0-2])$/')
                ),
                "label" => $this->translator->trans('Expiry month', [], Moneris::DOMAIN.'.fo.default')
            )
        )
        ->add(
            "cvdValue",
            "text",
            array(
                "constraints" => array(
                    new Constraints\NotBlank(),
                    new Constraints\Regex('/^(\d{3,4})$/')
                ),
                "label" => $this->translator->trans('Card verification code', [], Moneris::DOMAIN.'.fo.default')
            )
        );
    }
}