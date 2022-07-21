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
* Category loader for export process
* @category Nostress
* @package Nostress_Koongo
*
*
* List of new required params:
* media_url, store_locale, store_language, store_country, current_date, current_datetime, current_time
*
*/

namespace Nostress\Koongo\Model\Data\Loader;

class Category extends \Nostress\Koongo\Model\Data\Loader
{
    public function __construct(
        \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState,
        \Magento\Catalog\Model\Indexer\Product\Flat\State $productFlatState
    ) {
        parent::__construct($categoryFlatState, $productFlatState);
        $this->_init('Nostress\Koongo\Model\ResourceModel\Data\Loader\Category');
    }

    public function initAdapter()
    {
        parent::initAdapter();
        $this->basePart();
//         echo $this->adapter->getSelect()->__toString();
//         exit();
    }

    //***************************BASE PART**************************************
    protected function basePart()
    {
        $this->adapter->joinParentCategory();
        $this->adapter->joinCategoryPath();
        $this->adapter->orderByLevel();
        $this->adapter->addCategoryFilter();
    }

    //***************************COMMON PART**************************************

    protected function commonPart()
    {
    }
}
