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

namespace Nostress\Koongo\Helper\Data;

/**
 * Koongo connector order Helper.
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */
class Order extends \Nostress\Koongo\Helper\Data
{
    /**
     * Check if order has   at least one shipment
     *
     * @param Mage_Sales_Model_Order $order
     * @return boolean
     */
    public function orderHasShipment($order)
    {
        $shipmentCollection = $order->getShipmentsCollection();
        if (!$shipmentCollection || !isset($shipmentCollection)) {
            return false;
        }

        foreach ($shipmentCollection as $shipment) {
            return true;
        }
        return false;
    }
}
