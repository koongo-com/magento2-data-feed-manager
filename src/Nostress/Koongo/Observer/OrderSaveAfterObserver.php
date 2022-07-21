<?php
/**
 * Magento Module developed by NoStress Commerce
 *
 * NOTICE OF LICENSE
 *
 * This program is licensed under the Koongo software licence (by NoStress Commerce).
 * With the purchase, download of the software or the installation of the software
 * in your application you accept the licence agreement. The allowed usage is outlined in the
 * Koongo software licence which can be found under https://docs.koongo.com/display/koongo/License+Conditions
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at https://store.koongo.com/.
 *
 * See the Koongo software licence agreement for more details.
 * @copyright Copyright (c) 2017 NoStress Commerce (http://www.nostresscommerce.cz, http://www.koongo.com/)
 *
 */

/**
 * Observer for Order save after action for Kaas webhooks
 *
 * @category Nostress
 * @package Nostress_Koongo
 */

namespace Nostress\Koongo\Observer;

class OrderSaveAfterObserver extends \Nostress\Koongo\Observer\BaseObserver
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $order \Magento\Sales\Model\Order */
        $order = $observer->getEvent()->getOrder();

        $oldStatus = $order->getOrigData('status');
        $status = $order->getStatus();

        if (($oldStatus != $status)) {
            if ($status == 'canceled') {
                //Fire cancel webhook
                $cancelReason = __("Order canceled at Magento store.");
                $this->_addCancelOrderEvent($order, $cancelReason);

                //Update products related to the order
                $items = $order->getAllItems();
                $productSkusToUpdate = [];
                foreach ($items as $item) {
                    $productSkusToUpdate[] = $item->getSku();
                }
                //Product batch add/update webhook
                $this->_addBatchProductEvent($productSkusToUpdate);
            }
            //Commented out on 11.2.2021 - Refund actions should be triggered in this case
            // else if($status == 'closed')
            // {
            //     //Send cancel order webhook if order has no shipment
            //     if(!$this->helper->orderHasShipment($order))
            //     {
            //         //Fire cancel webhook if order was not shipped
            //         $cancelReason = __("Order closed at Magento store.");
            //         $this->_addCancelOrderEvent($order, $cancelReason);
            //     }
            // }
        }
    }
}
