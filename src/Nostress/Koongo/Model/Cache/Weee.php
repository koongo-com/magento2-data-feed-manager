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
 * Model for Koongo connector cache model - Weee
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Model\Cache;

class Weee extends \Nostress\Koongo\Model\Cache
{
    public function _construct()
    {
        $this->_init('Nostress\Koongo\Model\ResourceModel\Cache\Weee');
    }

    public function reloadWebsite($websiteId = null)
    {
        if (isset($websiteId)) {
            $storeId = $this->storeManager->getWebsite($websiteId)->getDefaultStore()->getId();
            $this->_getResource()->reloadWebsite($websiteId, $storeId);
        } else {
            $websites = $this->storeManager->getWebsites();
            foreach ($websites as $website) {
                $this->_getResource()->reloadWebsite($website->getWebsiteId(), $website->getDefaultStore()->getId());
            }
        }
    }
}
