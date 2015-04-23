<?php

namespace Moneris\Hook;

use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;

/**
 * Class MonerisHook
 * @package Moneris\Hook
 * @author Etienne PERRIERE <eperriere@openstudio.fr> - OpenStudio
 */
class MonerisHook extends BaseHook {

    public function onOrderPaymentGatewayBody(HookRenderEvent $event){
        $event->add(
            $this->render('card-form.html')
        );
    }

    public function onOrderPlacedBody(HookRenderEvent $event){
        $event->add(
            $this->render('moneris-receipt.html')
        );
    }
}