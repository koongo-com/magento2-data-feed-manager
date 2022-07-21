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

namespace Nostress\Koongo\Model\Data\Loader;

class Product extends \Nostress\Koongo\Model\Data\Loader
{
    public function __construct(
        \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState,
        \Magento\Catalog\Model\Indexer\Product\Flat\State $productFlatState
    ) {
        parent::__construct($categoryFlatState, $productFlatState);
        $this->_init('Nostress\Koongo\Model\ResourceModel\Data\Loader\Product');
    }

    public function initAdapter()
    {
        parent::initAdapter();

        $this->commonPart();
        $this->specificPart();

        // 		echo $this->adapter->getSelect()->__toString();
// 		exit();
    }

    public function getAttributesKeys()
    {
        $columns = $this->_getResource()->getAllColumns();
        return array_keys($columns);
    }

    //***************************BASE PART**************************************
    protected function specificPart()
    {
        $this->productsAll();
    }

    /**
     * Init sql.
     * Load all products from current store.
     */
    protected function productsAll()
    {
        $this->adapter->joinLeftCategoryFlat();
        $this->adapter->joinTaxonomy();
        $this->adapter->joinParentCategory();
    }

    //***************************COMMON PART**************************************

    protected function commonPart()
    {
        $this->adapter->joinProductEntity();
        $this->adapter->joinProductRelation();
        if ($this->adapter->isContentStagingAvailable()) {
            $this->adapter->joinParentProductEntity();
        }
        //$this->adapter->groupByProduct(); // Not necessary so far

        $this->adapter->addTypeCondition();
        $this->adapter->addVisibilityCondition();

        $this->adapter->addSortAttribute();
        $this->adapter->setProductsOrder();

        $this->stock();

        $this->adapter->joinTax();
        //products cache
        $this->adapter->joinProductCache();

        //products cache
        $this->adapter->joinParentProductCache();

        //price cache
        $this->adapter->joinPriceCache();

        $this->adapter->joinProfileCategoryCache();
        $this->adapter->joinWeee();

        $this->adapter->joinReviewsCache();

        $this->adapter->addAttributeFilter();
        $this->adapter->groupByProduct();
        // 		$this->adapter->joinCustomTables();
    }

    protected function stock()
    {
        $this->adapter->joinStock();
        $this->adapter->addStockCondition();
    }
}
