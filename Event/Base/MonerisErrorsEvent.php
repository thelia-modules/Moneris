<?php
/**
* This class has been generated by TheliaStudio
* For more information, see https://github.com/thelia-modules/TheliaStudio
*/

namespace Moneris\Event\Base;

use Thelia\Core\Event\ActionEvent;
use Moneris\Model\MonerisErrors;

/**
* Class MonerisErrorsEvent
* @package Moneris\Event\Base
* @author TheliaStudio
*/
class MonerisErrorsEvent extends ActionEvent
{
    protected $id;
    protected $orderId;
    protected $message;
    protected $monerisErrors;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getOrderId()
    {
        return $this->orderId;
    }

    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    public function getMonerisErrors()
    {
        return $this->monerisErrors;
    }

    public function setMonerisErrors(MonerisErrors $monerisErrors)
    {
        $this->monerisErrors = $monerisErrors;

        return $this;
    }
}