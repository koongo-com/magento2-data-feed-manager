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
 * Observer for Product Import Bunch Delete After Observer for Kaas webhooks
 *
 * @category Nostress
 * @package Nostress_Koongo
 */

namespace Nostress\Koongo\Observer;

class ProductImportBunchDeleteAfterObserver extends \Nostress\Koongo\Observer\BaseObserver
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $idsToDelete = $observer->getIdsToDelete();

        //Product batch delete webhook
        $this->_addBatchDeleteProductEvent($idsToDelete);
    }
}
