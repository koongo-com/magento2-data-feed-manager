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
* Data loader for export process
* @category Nostress
* @package Nostress_Koongo
*
*
* List of new required params:
* media_url, store_locale, store_language, store_country, current_date, current_datetime, current_time
*
*/

namespace Nostress\Koongo\Model\Data;

abstract class Loader extends \Nostress\Koongo\Model\AbstractModel
{
    protected $adapter;
    /**
     * @var \Indexer\Product\Flat\State
     */
    protected $productFlatState;
    /**
     * @var \Indexer\Category\Flat\State
     */
    protected $categoryFlatState;

    public function __construct(
        \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState,
        \Magento\Catalog\Model\Indexer\Product\Flat\State $productFlatState
    ) {
        $this->_init('Nostress\Koongo\Model\ResourceModel\Data\Loader');
        $this->categoryFlatState = $categoryFlatState;
        $this->productFlatState = $productFlatState;
    }

    public function init($params)
    {
        $this->setData($params);
        $this->initAdapter();
    }

    public function loadBatch()
    {
        return $this->adapter->loadBatch();
    }

    public function loadAll()
    {
        return $this->adapter->loadAll();
    }

    protected function initAdapter()
    {
        $this->adapter = $this->_getResource();
        $this->adapter->setStoreId($this->getStoreId());
        $this->adapter->setProfileId($this->getProfileId());
        $this->adapter->setAttributes($this->getAttributes());
        $data = $this->getData();
        unset($data["store_id"]);
        unset($data["profile_id"]);
        unset($data["attributes"]);
        $this->adapter->setData($data);
        $this->adapter->init();
    }
}
