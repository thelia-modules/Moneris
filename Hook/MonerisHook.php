<?php
/*************************************************************************************/
/*      This file is part of the Thelia package.                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace Moneris\Hook;

use Moneris\Model\MonerisErrorsQuery;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;

/**
 * Class MonerisHook
 * @package Moneris\Hook
 * @author Etienne PERRIERE <eperriere@openstudio.fr> - OpenStudio
 */
class MonerisHook extends BaseHook
{

    public function onOrderPaymentGatewayBody(HookRenderEvent $event)
    {
        $event->add(
            $this->render('card-form.html')
        );
    }

    public function onOrderPlacedBody(HookRenderEvent $event)
    {
        $event->add(
            $this->render('moneris-receipt.html')
        );
    }

    public function showPaymentInfo(HookRenderEvent $event)
    {
        $orderId = $event->getArgument('order_id');

        if (null !== $order = MonerisErrorsQuery::create()->findOneByOrderId($orderId)) {
            $event->add($this->render(
                'order-info.html',
                [
                    'order_id' => $orderId
                ]
            ));
        }
    }
}
