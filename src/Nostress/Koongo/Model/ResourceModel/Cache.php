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
* ResourceModel for Koongo Connector cache
*
* @category Nostress
* @package Nostress_Koongo
*
*/

namespace Nostress\Koongo\Model\ResourceModel;

class Cache extends \Nostress\Koongo\Model\ResourceModel\Data\Loader
{
    const STARTED = "started";
    const FINISHED = "finished";

    protected $_cacheName = '';

    protected function defineColumns()
    {
        parent::defineColumns();
    }

    public function reload($storeId)
    {
        $this->logStatus(self::STARTED, $storeId);
        $this->setStoreId($storeId);
        $this->init();
        $this->reloadTable();
        $this->logStatus(self::FINISHED, $storeId);
    }

    public function init()
    {
        $this->defineColumns();
    }

    protected function logStatus($status, $storeId)
    {
        $this->helper->log(__("%1 cache reload has %2 for store #%3", $this->_cacheName, $status, $storeId));
    }

    public function getCacheColumns($type = null)
    {
        if (!isset($this->_columns)) {
            $this->defineColumns();
        }
        return [];
    }

//     protected function reloadTable()
//     {

//     }
}
