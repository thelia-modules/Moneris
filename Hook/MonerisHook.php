<?php

namespace Moneris\Hook;

use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;

class MonerisHook extends BaseHook {

    public function onOrderInvoiceBottom(HookRenderEvent $event)
    {
        $event->add(
            $this->render('order-placed-body.html')
        );
    }
}